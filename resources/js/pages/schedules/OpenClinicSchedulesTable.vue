```vue
<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import OpenClinicSchedulesActionsButtons from '@/components/buttons/OpenClinicSchedulesActionsButtons.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import LoadingSpinner from '@/components/ui/spinner/Spinner.vue';
import { Button } from '@/components/ui/button';
import type { AppPageProps } from '@/types/index';
import type { OpenClinicScheduleClinic, OpenClinicSchedulePeriodOption, OpenClinicScheduleResponsibleOption, OpenClinicScheduleRow, OpenClinicSchedulesFilters, } from '@/types/schedule/openClinicSchedules';
import { formatDateBr } from '@/src/utils/formatters';
import { Link, router, usePage } from '@inertiajs/vue3';
import { AgGridVue } from 'ag-grid-vue3';
import { ArrowLeft, Pencil, X } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import OpenClinicScheduleCard from './components/OpenClinicScheduleCard.vue';

const emit = defineEmits<{
    (e: 'create', clinicId: number, periodId: number | null): void;
    (e: 'edit', slot: OpenClinicScheduleRow): void;
    (e: 'remove', slot: OpenClinicScheduleRow): void;
    (e: 'removeMultiple', slots: OpenClinicScheduleRow[]): void;
    (e: 'editMultiple', slots: OpenClinicScheduleRow[]): void;
    (e: 'addStudents', slots: OpenClinicScheduleRow[]): void;
}>();

type SortField = 'created_at' | 'date' | 'start_time';
type SortDir = 'asc' | 'desc';

interface PaginatedSlots {
    data: OpenClinicScheduleRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

type OpenClinicSchedulesPage = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    periods: OpenClinicSchedulePeriodOption[];
    slots: PaginatedSlots;
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;

const page = usePage<OpenClinicSchedulesPage>();

const clinic = computed(() => page.props.clinic);
const periods = computed(() => page.props.periods);
const filters = computed(() => page.props.filters);

const slots = ref<OpenClinicScheduleRow[]>([]);

const selectedRows = ref<OpenClinicScheduleRow[]>([]);

const gridApi = ref<any>(null);

const isLoading = ref(false);
const isUpdatingFromServer = ref(false);

const pageNumber = ref(1);
const perPage = ref(10);
const total = ref(0);
const totalPages = ref(0);

const sortField = ref<SortField>('created_at');
const sortDir = ref<SortDir>('desc');

const form = reactive({
    period_id: null as number | null,
    date: '' as string,
});

const hasPeriodSelected = computed(() => !!form.period_id);

watch(
    () => page.props.slots,
    (value) => {
        slots.value = [...value.data];

        pageNumber.value = value.current_page;
        perPage.value = value.per_page;
        total.value = value.total;
        totalPages.value = value.last_page;
    },
    {
        immediate: true,
    },
);

watch(
    filters,
    (next) => {
        isUpdatingFromServer.value = true;

        form.period_id = next?.period_id ?? null;
        form.date = next?.date ?? '';

        setTimeout(() => {
            isUpdatingFromServer.value = false;
        }, 0);
    },
    {
        deep: true,
        immediate: true,
    },
);

const periodOptions = computed(() =>
    periods.value.map((period) => ({
        label: period.label,
        value: period.id,
    })),
);

function isSlotSelected(slot: OpenClinicScheduleRow): boolean {
    return selectedRows.value.some(
        (selected) => selected.id === slot.id,
    );
}

function canSelectSlot(slot: OpenClinicScheduleRow): boolean {
    if (isSlotSelected(slot)) {
        return true;
    }

    return !selectedRows.value.some(
        (selected) => selected.date === slot.date,
    );
}

function toggleSlotSelection(slot: OpenClinicScheduleRow): void {
    const index = selectedRows.value.findIndex(
        (selected) => selected.id === slot.id,
    );

    if (index !== -1) {
        selectedRows.value.splice(index, 1);
        return;
    }

    if (!canSelectSlot(slot)) {
        return;
    }

    selectedRows.value.push(slot);
}

function isGridRowSelectable(rowNode: any): boolean {
    const slot = rowNode.data as OpenClinicScheduleRow | undefined;

    if (!slot) {
        return false;
    }

    return canSelectSlot(slot);
}

function onSelectionChanged(): void {
    if (!gridApi.value) {
        return;
    }

    const selectedNodes = gridApi.value.getSelectedNodes();

    selectedRows.value = selectedNodes
        .map((node: any) => node.data)
        .filter(Boolean);
}

function onGridReady(params: any): void {
    gridApi.value = params.api;
}

watch(slots, () => {
    selectedRows.value = [];

    if (gridApi.value) {
        gridApi.value.deselectAll();
    }
});

function applyFilters(): void {
    isLoading.value = true;

    router.get(
        `/schedules/open-clinics/${clinic.value.id}`,
        {
            page: pageNumber.value,
            per_page: perPage.value,
            sort_field: sortField.value,
            sort_dir: sortDir.value,
            period_id: form.period_id
                ? Number(form.period_id)
                : undefined,
            date: form.date || undefined,
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
}

watch(
    () => [form.period_id, form.date],
    () => {
        if (isUpdatingFromServer.value) {
            return;
        }

        pageNumber.value = 1;

        applyFilters();
    },
);

function clearFilters(): void {
    form.period_id = null;
    form.date = '';

    applyFilters();
}

function goToPage(page: number): void {
    if (page < 1 || page > totalPages.value) {
        return;
    }

    pageNumber.value = page;

    applyFilters();
}

const fromTo = computed(() => {
    const pagination = page.props.slots;

    if (!pagination.total) {
        return '0';
    }

    return `${pagination.from}-${pagination.to} de ${pagination.total}`;
});

const columnDefs = [
    {
        headerCheckboxSelection: true,
        checkboxSelection: true,
        width: 50,
        pinned: 'left',
        headerCheckboxSelectionFilteredOnly: true,
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
        valueFormatter: (
            params: {
                data: OpenClinicScheduleRow;
            },
        ) =>
            `${params.data.start_time.slice(0, 5)} às ${params.data.end_time.slice(0, 5)}`,
    },

    {
        headerName: 'Período',
        field: 'period_label',
        flex: 2,
        sortable: true,
        filter: true,
    },

    {
        headerName: 'Responsável',
        field: 'responsible_names',
        flex: 1.5,
        sortable: true,
        filter: true,
        valueFormatter: (params: any) =>
            params.value?.join(', ') ?? '—',
    },

    {
        headerName: 'Vagas',
        field: 'available_slots',
        flex: 1,
        sortable: true,
        filter: true,
    },

    {
        headerName: 'Ações',
        colId: 'actions',
        flex: 1,
        sortable: false,
        filter: false,
        cellClass: 'cell-center',
        cellRenderer: OpenClinicSchedulesActionsButtons,
        cellRendererParams: {
            onEdit: (row: OpenClinicScheduleRow) =>
                emit('edit', row),

            onRemove: (row: OpenClinicScheduleRow) =>
                emit('remove', row),

            onAddStudents: (row: OpenClinicScheduleRow) =>
                emit('addStudents', row),
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
                <h1
                    class="text-xl font-semibold tracking-tight text-gray-900"
                >
                    Agendas abertas da clínica
                </h1>

                <p class="text-sm text-gray-500">
                    {{ clinic.name }}
                </p>
            </div>

            <div
                class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:justify-end"
            >
                <CreateButton
                    v-if="hasPeriodSelected"
                    label="Cadastrar novo dia"
                    icon="Plus"
                    :disabled="!form.period_id"
                    class="w-full sm:w-auto"
                    @click="
                        emit(
                            'create',
                            clinic.id,
                            form.period_id,
                        )
                    "
                />

                <Link
                    href="/schedules/open-clinics"
                    class="w-full sm:w-auto"
                >
                    <Button
                        variant="outline"
                        class="inline-flex h-9 w-full cursor-pointer items-center justify-center gap-2 sm:w-auto"
                    >
                        <ArrowLeft
                            class="h-4 w-4 shrink-0"
                        />

                        <span>
                            Voltar para clínicas abertas
                        </span>
                    </Button>
                </Link>
            </div>
        </div>

        <div
            class="mb-6 grid items-end gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-4"
        >
            <div class="sm:col-span-2">
                <AppMultiselect
                    id="period_id"
                    v-model="form.period_id"
                    :options="periodOptions"
                    field-label="Período"
                    label="label"
                    value-prop="value"
                    :searchable="true"
                    :close-on-select="true"
                    :can-clear="true"
                    :append-to-body="true"
                    placeholder="Todos os períodos"
                />
            </div>

            <div>
                <BaseInput
                    id="date"
                    v-model="form.date"
                    label="Data"
                    type="date"
                />
            </div>

            <div class="flex h-full items-end">
                <Button
                    variant="outline"
                    class="inline-flex h-9 w-full cursor-pointer items-center justify-center gap-2 rounded-lg border-gray-200 bg-white text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 active:scale-[0.98]"
                    @click="clearFilters"
                >
                    <X class="h-4 w-4" />
                    Limpar
                </Button>
            </div>
        </div>

        <div
            v-if="!hasPeriodSelected"
            class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
        >
            Selecione um período e filtre para visualizar as datas de agendas abertas.
        </div>

        <div
            v-else-if="!slots.length"
            class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
        >
            Nenhuma agenda encontrada para o período selecionado.
        </div>

        <div
            v-else-if="!isLoading"
            class="overflow-x-auto"
        >
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
                        @click="
                            emit(
                                'editMultiple',
                                selectedRows,
                            )
                        "
                    >
                        <Pencil class="h-4 w-4" />

                        Editar selecionados
                        ({{ selectedRows.length }})
                    </Button>

                    <DeleteButton
                        :loading="isLoading"
                        :label="`Excluir selecionados (${selectedRows.length})`"
                        @click="
                            emit(
                                'removeMultiple',
                                selectedRows,
                            )
                        "
                    />
                </div>

                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="slots"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :isRowSelectable="isGridRowSelectable"
                    :components="{
                        OpenClinicSchedulesActionsButtons,
                    }"
                    rowSelection="multiple"
                    :rowMultiSelectWithClick="false"
                    :suppressRowClickSelection="true"
                    @selection-changed="onSelectionChanged"
                    @grid-ready="onGridReady"
                />
            </div>

            <div class="space-y-3 md:hidden">
                <div
                    v-if="selectedRows.length"
                    class="flex flex-col gap-2"
                >
                    <Button
                        variant="outline"
                        class="w-full"
                        @click="
                            emit(
                                'editMultiple',
                                selectedRows,
                            )
                        "
                    >
                        <Pencil class="h-4 w-4" />

                        Editar selecionados
                        ({{ selectedRows.length }})
                    </Button>

                    <DeleteButton
                        :loading="isLoading"
                        :label="`Excluir selecionados (${selectedRows.length})`"
                        class="w-full"
                        @click="
                            emit(
                                'removeMultiple',
                                selectedRows,
                            )
                        "
                    />
                </div>

                <OpenClinicScheduleCard
                    v-for="slot in slots"
                    :key="slot.id"
                    :slot="slot"
                    :selected="isSlotSelected(slot)"
                    :selectable="canSelectSlot(slot)"
                    @select="toggleCardSelection(slot)"
                    @edit="emit('edit', $event)"
                    @remove="emit('remove', $event)"
                    @add-students="emit('addStudents', [$event])"
                />
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center gap-2 rounded border border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600"
        >
            <LoadingSpinner
                v-if="isLoading"
                class="h-5 w-5"
            />

            <span>
                Carregando agendas...
            </span>
        </div>

        <div
            v-if="totalPages > 0"
            class="mt-4 flex flex-wrap items-center justify-between gap-2"
        >
            <p class="text-sm text-gray-600">
                {{ fromTo }}
            </p>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    :disabled="pageNumber <= 1"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                    @click="goToPage(pageNumber - 1)"
                >
                    Anterior
                </button>

                <template
                    v-for="p in totalPages"
                    :key="p"
                >
                    <button
                        v-if="
                            p === 1 ||
                            p === totalPages ||
                            (p >= pageNumber - 2 &&
                                p <= pageNumber + 2)
                        "
                        type="button"
                        :class="[
                            'rounded-md px-3 py-1.5 text-sm transition',
                            p === pageNumber
                                ? 'bg-sky-600 text-white shadow'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
                        @click="goToPage(p)"
                    >
                        {{ p }}
                    </button>

                    <span
                        v-else-if="
                            p === pageNumber - 3 ||
                            p === pageNumber + 3
                        "
                        class="px-1"
                    >
                        …
                    </span>
                </template>

                <button
                    type="button"
                    :disabled="pageNumber >= totalPages"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                    @click="goToPage(pageNumber + 1)"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>
