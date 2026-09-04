<script setup lang="ts">
import OpenClinicSchedulesEnrollmentActionsButtons from '@/components/buttons/OpenClinicSchedulesEnrollmentActionsButtons.vue';
import { Button } from '@/components/ui/button';
import type { AppPageProps } from '@/types/index';
import type {
    OpenClinicScheduleEnrollmentClinic,
    OpenClinicScheduleEnrollmentRow,
    OpenClinicSchedulesEnrollmentFilters,
} from '@/types/schedule-enrollment/openClinicSchedulesEnrollment';
import { Link, router, usePage } from '@inertiajs/vue3';
import { AgGridVue } from 'ag-grid-vue3';
import { ArrowLeft, CheckCheck, X } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import { formatDateBr } from '@/src/utils/formatters';

const emit = defineEmits<{
    (e: 'enroll', slot: OpenClinicScheduleEnrollmentRow): void;
    (e: 'enrollMultiple', slots: OpenClinicScheduleEnrollmentRow[]): void;
}>();

const isUpdatingFromServer = ref(false);

type OpenClinicSchedulesPage = AppPageProps<{
    clinic: OpenClinicScheduleEnrollmentClinic;
    slots: OpenClinicScheduleEnrollmentRow[];
    filters: OpenClinicSchedulesEnrollmentFilters;
}>;

const page = usePage<OpenClinicSchedulesPage>();
const selectedRows = ref<OpenClinicScheduleEnrollmentRow[]>([]);

const clinic = computed(() => page.props.clinic);
const slots = computed(() => page.props.slots);
const filters = computed(() => page.props.filters);
console.log(slots);

console.log('oi',
    slots.value.map(slot => ({
        id: slot.id,
        responsible_names: slot.responsible_names,
    }))
);
const gridApi = ref<any>(null);

const form = reactive({
    period_id: null as number | null,
    date: '' as string,
});

watch(
    filters,
    (next) => {
        isUpdatingFromServer.value = true;

        form.date = next?.date ?? '';

        setTimeout(() => {
            isUpdatingFromServer.value = false;
        }, 0);
    },
    { deep: true, immediate: true },
);

function onGridReady(params: any) {
    gridApi.value = params.api;
}

function onSelectionChanged() {
    if (!gridApi.value) return;
    selectedRows.value = gridApi.value.getSelectedRows();
}

function applyFilters() {
    router.get(
        `/schedule-enrollment/open-clinic/${clinic.value.id}`,
        {
            date: form.date || undefined,   
        },
        { preserveState: true, replace: true },
    );
}

function isRowSelectable(rowNode: any) {
    return (
        rowNode.data?.allow_student_booking &&
        !rowNode.data?.is_enrolled
    );
}

watch(slots, () => {
    if (gridApi.value) {
        gridApi.value.deselectAll();
    }
    selectedRows.value = [];
});

watch(
    () => form.date,
    () => {
        if (isUpdatingFromServer.value) return;
        applyFilters();
    }
);

function clearFilters() {
    form.period_id = null;
    form.date = '';
    applyFilters();
}

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
        pinned: 'left',
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
                class="ag-theme-alpine relative"
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
            </div>
        </div>
    </div>
</template>
