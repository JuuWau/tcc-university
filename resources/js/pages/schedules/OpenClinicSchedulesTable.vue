<script setup lang="ts">
import CreateButton from '@/components/buttons/CreateButton.vue';
import OpenClinicSchedulesActionsButtons from '@/components/buttons/OpenClinicSchedulesActionsButtons.vue';
import { Button } from '@/components/ui/button';
import type { AppPageProps } from '@/types/index';
import type {
    OpenClinicScheduleClinic,
    OpenClinicSchedulePeriodOption,
    OpenClinicScheduleResponsibleOption,
    OpenClinicScheduleRow,
    OpenClinicSchedulesFilters,
} from '@/types/schedule/openClinicSchedules';
import { Link, router, usePage } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { AgGridVue } from 'ag-grid-vue3';
import { ArrowLeft, Filter, Pencil, Trash2, X } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import LoadingSpinner from '@/components/ui/spinner/Spinner.vue';
import { formatDateBr } from '@/src/utils/formatters';

const emit = defineEmits<{
    (e: 'create', clinicId: number, periodId: number | null): void;
    (e: 'edit', slot: OpenClinicScheduleRow): void;
    (e: 'remove', slot: OpenClinicScheduleRow): void;
    (e: 'removeMultiple', slots: OpenClinicScheduleRow[]): void;
    (e: 'editMultiple', slots: OpenClinicScheduleRow[]): void;
}>();

const hasPeriodSelected = computed(() => !!form.period_id);
const isUpdatingFromServer = ref(false);

type OpenClinicSchedulesPage = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    periods: OpenClinicSchedulePeriodOption[];
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;

const page = usePage<OpenClinicSchedulesPage>();
const selectedRows = ref<OpenClinicScheduleRow[]>([]);

const clinic = computed(() => page.props.clinic);
const periods = computed(() => page.props.periods);
const slots = computed(() => page.props.slots);
const filters = computed(() => page.props.filters);
console.log(slots);

const gridApi = ref<any>(null);

const form = reactive({
    period_id: null as number | null,
    date: '' as string,
});

const isLoading = ref(false);

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
    { deep: true, immediate: true },
);

const periodOptions = computed(() =>
    periods.value.map((p) => ({
        label: p.label,
        value: p.id,
    })),
);

function onGridReady(params: any) {
    gridApi.value = params.api;
}

function onSelectionChanged() {
    if (!gridApi.value) return;
    selectedRows.value = gridApi.value.getSelectedRows();
}

function applyFilters() {
    isLoading.value = true;

    router.get(
        `/schedules/open-clinics/${clinic.value.id}`,
        {
            period_id: form.period_id ? Number(form.period_id) : undefined,
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

watch(slots, () => {
    if (gridApi.value) {
        gridApi.value.deselectAll();
    }
    selectedRows.value = [];
});

watch(
    () => [form.period_id, form.date],
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
        checkboxSelection: true,
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
        valueFormatter: (params: { data: OpenClinicScheduleRow }) =>
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
        field: 'responsible_name',
        flex: 1.5,
        sortable: true,
        filter: true,
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
            onEdit: (row: OpenClinicScheduleRow) => emit('edit', row),
            onRemove: (row: OpenClinicScheduleRow) => emit('remove', row),
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
                <CreateButton 
                    v-if="hasPeriodSelected"
                    label="Cadastrar novo dia"
                    icon="Plus"
                    class="w-full sm:w-auto"
                    :disabled="!form.period_id"
                    @click="emit('create', clinic.id, form.period_id)"
                />
                <Link href="/schedules/open-clinics">
                    <Button variant="outline" class="w-full sm:w-auto">
                        <ArrowLeft class="h-4 w-4" />
                        Voltar para clínicas abertas
                    </Button>
                </Link>
            </div>
        </div>

        <div
            class="mb-6 grid items-end gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-4"
        >
            <div class="sm:col-span-2">
                <label
                    for="period_id"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Período
                </label>
                <Multiselect
                    id="period_id"
                    v-model="form.period_id"
                    :options="periodOptions"
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
                    class="flex w-full items-center justify-center gap-2"
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

        <div v-else-if="!isLoading" class="overflow-x-auto">
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
                        @click="emit('editMultiple', selectedRows)"
                    >
                        <Pencil class="h-4 w-4" />
                        Editar selecionados ({{ selectedRows.length }})
                    </Button>
                    <Button
                        variant="destructive"
                        @click="emit('removeMultiple', selectedRows)"
                    >
                        <Trash2 class="h-4 w-4" />
                        Excluir selecionados ({{ selectedRows.length }})
                    </Button>
                </div>
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="slots"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :components="{ OpenClinicSchedulesActionsButtons }"
                    rowSelection="multiple"
                    :rowMultiSelectWithClick="true"
                    @selection-changed="onSelectionChanged"
                    @grid-ready="onGridReady"
                />
            </div>
        </div>
        <div
            v-else
            class="flex flex-col items-center justify-center gap-2 rounded border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600 text-center"
        >
            <LoadingSpinner v-if="isLoading" class="h-5 w-5" />
            <span>Carregando agendas...</span>
        </div>
    </div>
</template>
