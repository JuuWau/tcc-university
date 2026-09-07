<script setup lang="ts">
import OpenClinicSchedulesEnrollmentActionsButtons from '@/components/buttons/OpenClinicSchedulesEnrollmentActionsButtons.vue';
import { RefreshTableKey } from '@/keys/schedule-enrollment/scheduleSlotEnrollmentKeys';
import { Button } from '@/components/ui/button';
import axios from 'axios';
import type { AppPageProps } from '@/types/index';
import type {
    OpenClinicScheduleEnrollmentClinic,
    OpenClinicScheduleEnrollmentRow,
    OpenClinicSchedulesEnrollmentFilters,
} from '@/types/schedule-enrollment/openClinicSchedulesEnrollment';
import { Link, usePage } from '@inertiajs/vue3';
import { AgGridVue } from 'ag-grid-vue3';
import { ArrowLeft, CheckCheck, X } from 'lucide-vue-next';
import { computed, inject, onMounted, reactive, ref, watch } from 'vue';
import { formatDateBr } from '@/src/utils/formatters';
import OpenClinicScheduleEnrollmentSlotCard from './components/OpenClinicScheduleEnrollmentSlotCard.vue';

const emit = defineEmits<{
    (e: 'enroll', slot: OpenClinicScheduleEnrollmentRow): void;
    (e: 'enrollMultiple', slots: OpenClinicScheduleEnrollmentRow[]): void;
}>();

const refreshTableRef = inject(RefreshTableKey);

type OpenClinicSchedulesPage = AppPageProps<{
    clinic: OpenClinicScheduleEnrollmentClinic;
    slots: [];
    filters: OpenClinicSchedulesEnrollmentFilters;
}>;

const page = usePage<OpenClinicSchedulesPage>();
const selectedRows = ref<OpenClinicScheduleEnrollmentRow[]>([]);

const clinic = computed(() => page.props.clinic);
const filters = computed(() => page.props.filters);
const slots = ref<OpenClinicScheduleEnrollmentRow[]>([]);
const loading = ref(false);
const pageNumber = ref(1);
const perPage = ref(10);
const total = ref(0);
const totalPages = ref(0);
type SortField = 'date' | 'start_time' | 'end_time' | 'created_at';
type SortDir = 'asc' | 'desc';
const sortField = ref<SortField>('date');
const sortDir = ref<SortDir>('asc');
const gridApi = ref<any>(null);

const form = reactive({
    period_id: null as number | null,
    date: '' as string,
});

watch(filters, (next) => {
    form.date = next?.date ?? '';
}, { deep: true, immediate: true });

function onGridReady(params: any) {
    gridApi.value = params.api;
}

function onSelectionChanged() {
    if (!gridApi.value) return;
    selectedRows.value = gridApi.value.getSelectedRows();
}

function applyFilters() {
    pageNumber.value = 1;
    fetchSlots();
}

async function fetchSlots() {
    loading.value = true;

    try {
        const { data } = await axios.get<{
            slots: {
                data: OpenClinicScheduleEnrollmentRow[];
                current_page: number;
                last_page: number;
                per_page: number;
                total: number;
            };
        }>(`/schedule-enrollment/open-clinic/${clinic.value.id}/table`, {
            params: {
                page: pageNumber.value,
                per_page: perPage.value,
                sort_field: sortField.value,
                sort_dir: sortDir.value,
                date: form.date || undefined,
            },
        });

        slots.value = data.slots.data;
        pageNumber.value = data.slots.current_page;
        perPage.value = data.slots.per_page;
        total.value = data.slots.total;
        totalPages.value = data.slots.last_page;
    } catch {
        slots.value = [];
        total.value = 0;
        totalPages.value = 0;
    } finally {
        loading.value = false;
    }
}

function isRowSelectable(rowNode: any) {
    return (
        rowNode.data?.allow_student_booking &&
        !rowNode.data?.is_enrolled
    );
}

function isSlotSelected(slot: OpenClinicScheduleEnrollmentRow): boolean {
    return selectedRows.value.some((selected) => selected.id === slot.id);
}

function canSelectSlot(slot: OpenClinicScheduleEnrollmentRow): boolean {
    if (isSlotSelected(slot)) return true;

    return (
        slot.allow_student_booking &&
        !slot.is_enrolled &&
        !selectedRows.value.some((selected) => selected.date === slot.date)
    );
}

function toggleCardSelection(slot: OpenClinicScheduleEnrollmentRow): void {
    const index = selectedRows.value.findIndex(
        (selected) => selected.id === slot.id,
    );

    if (index !== -1) {
        selectedRows.value.splice(index, 1);
        return;
    }

    if (canSelectSlot(slot)) {
        selectedRows.value.push(slot);
    }
}

watch(slots, () => {
    if (gridApi.value) {
        gridApi.value.deselectAll();
    }
    selectedRows.value = [];
});

watch(() => form.date, applyFilters);

watch([pageNumber, perPage, sortField, sortDir], () => {
    if (!loading.value) fetchSlots();
});

function clearFilters() {
    form.period_id = null;
    form.date = '';
}

function goToPage(nextPage: number) {
    if (nextPage >= 1 && nextPage <= totalPages.value) {
        pageNumber.value = nextPage;
    }
}

const fromTo = computed(() => {
    if (!total.value) return '0';
    const from = (pageNumber.value - 1) * perPage.value + 1;
    const to = Math.min(pageNumber.value * perPage.value, total.value);
    return `${from}-${to} de ${total.value}`;
});

onMounted(() => {
    fetchSlots();

    if (refreshTableRef) {
        refreshTableRef.value = fetchSlots;
    }
});

const columnDefs = [
    {
        headerCheckboxSelection: true,

        checkboxSelection: (params: any) => {
            return (
                params.data?.allow_student_booking &&
                !params.data?.is_enrolled
            );
        },

        showDisabledCheckboxes: false,

        width: 50,
        pinned: 'left' as const,
    },
    {
        headerName: 'Data',
        field: 'date',
        flex: 1,
        sortable: true,
        filter: true,
        valueFormatter: (params: { value: string }) =>
            formatDateBr(params.value),
    },
    {
        headerName: 'Horário',
        field: 'start_time',
        flex: 1,
        sortable: true,
        filter: true,
        valueFormatter: (params: { data: OpenClinicScheduleEnrollmentRow }) =>
            `${params.data.start_time.slice(0, 5)} às ${params.data.end_time.slice(0, 5)}`,
    },
    {
        headerName: 'Período',
        field: 'period_label',
        flex: 2,
        sortable: true,
        filter: false,
    },
    {
        headerName: 'Responsável',
        flex: 1.5,
        sortable: true,
        filter: false,
        valueGetter: (params: any) => {
            const names = params.data?.responsible_names;

            return Array.isArray(names)
                ? names.join(', ')
                : names ?? '—';
        },
    },
    {
        headerName: 'Vagas',
        field: 'available_slots',
        flex: 1,
        sortable: true,
        filter: false,
    },
    {
        headerName: 'Status',
        field: 'is_enrolled',
        flex: 1,
        cellRenderer: (params: any) => {

            if (params.data.is_enrolled) {
                return `
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">
                        Inscrito
                    </span>
                `;
            } else {
                return `
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">
                    Não inscrito
                </span>
            `;
            }
        },
    },
    {
        headerName: 'Ações',
        colId: 'actions',
        flex: 1,
        sortable: false,
        filter: false,
        cellClass: 'cell-center',
        cellRenderer: OpenClinicSchedulesEnrollmentActionsButtons,
        cellRendererParams: {
            onEnroll: (row: OpenClinicScheduleEnrollmentRow) => {
                emit('enroll', row);
            },
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Agendas abertas da clínica
                </h1>
                <p class="text-sm text-gray-500">
                    {{ clinic.name }}
                </p>
            </div>
            <div class="flex justify-end gap-2">
                <Link href="/schedule-enrollment/open-clinics" class="inline-flex">
                    <Button variant="outline" class="w-full sm:w-auto">
                        <ArrowLeft class="h-4 w-4" />
                        Voltar para clínicas abertas
                    </Button>
                </Link>
            </div>
        </div>

        <div
            class="mb-6 grid items-end gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-3"
        >

            <div class="sm:col-span-2">
                <label
                    for="date"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Data
                </label>
                <input
                    id="date"
                    v-model="form.date"
                    type="date"
                    class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                />
            </div>

            <div class="flex h-full flex-col justify-end gap-2">
                <Button
                    variant="outline"
                    class="flex w-full items-center justify-center gap-2 cursor-pointer" 
                    @click="clearFilters"
                >
                    <X class="h-4 w-4" />
                    Limpar
                </Button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative hidden md:block"
                style="height: 500px; width: 100%"
            >
                <div
                    v-if="selectedRows.length"
                    class="mb-4 flex justify-end gap-2"
                >
                    <Button
                        variant="outline"
                        @click="emit('enrollMultiple', selectedRows)"
                    >
                        <CheckCheck class="h-4 w-4" />
                        Inscrever ({{ selectedRows.length }})
                    </Button>
                </div>
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="slots"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :components="{ OpenClinicSchedulesEnrollmentActionsButtons }"
                    rowSelection="multiple"
                    :rowMultiSelectWithClick="false"
                    :suppressRowClickSelection="true"
                    :isRowSelectable="isRowSelectable"
                    @selection-changed="onSelectionChanged"
                    @grid-ready="onGridReady"
                />

                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70"
                >
                    <span
                        class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                    ></span>
                    Carregando horários
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                <div
                    v-if="selectedRows.length"
                    class="flex justify-end"
                >
                    <Button
                        variant="outline"
                        class="w-full"
                        @click="emit('enrollMultiple', selectedRows)"
                    >
                        <CheckCheck class="h-4 w-4" />
                        Inscrever ({{ selectedRows.length }})
                    </Button>
                </div>

                <OpenClinicScheduleEnrollmentSlotCard
                    v-for="slot in slots"
                    :key="slot.id"
                    :schedule-slot="slot"
                    :selected="isSlotSelected(slot)"
                    :selectable="canSelectSlot(slot)"
                    @select="toggleCardSelection(slot)"
                    @enroll="emit('enroll', $event)"
                />

                <div
                    v-if="!loading && slots.length === 0"
                    class="rounded-xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500"
                >
                    Nenhum horário encontrado.
                </div>
            </div>
        </div>

        <div
            v-if="totalPages > 0"
            class="mt-4 flex flex-wrap items-center justify-between gap-2"
        >
            <p class="text-sm text-gray-600">{{ fromTo }}</p>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    :disabled="pageNumber <= 1"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm disabled:opacity-50"
                    @click="goToPage(pageNumber - 1)"
                >
                    Anterior
                </button>

                <button
                    v-for="currentPage in totalPages"
                    :key="currentPage"
                    type="button"
                    :class="[
                        'rounded px-3 py-1 text-sm',
                        currentPage === pageNumber
                            ? 'bg-sky-600 text-white'
                            : 'text-gray-600 hover:bg-gray-100',
                    ]"
                    @click="goToPage(currentPage)"
                >
                    {{ currentPage }}
                </button>

                <button
                    type="button"
                    :disabled="pageNumber >= totalPages"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm disabled:opacity-50"
                    @click="goToPage(pageNumber + 1)"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>
