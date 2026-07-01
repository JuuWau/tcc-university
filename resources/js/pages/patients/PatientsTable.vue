<script setup lang="ts">
import StatusBadge from '@/components/badges/StatusBadge.vue';
import { PATIENT_STATUS } from '@/types/patient/patient';
import CreateButton from '@/components/buttons/CreateButton.vue';
import PatientTableActionsButtons from '@/components/buttons/PatientTableActionsButtons.vue';
import { RefreshTableKey } from '@/keys/patients/patientKeys';
import type {
    PatientStatusKey,
    PatientWithInvite,
} from '@/types/patient/patient';
import { AgGridVue } from 'ag-grid-vue3';
import axios from 'axios';
import { computed, inject, onMounted, ref, watch } from 'vue';
import ImportExcelButton from '@/components/buttons/ImportExcelButton.vue';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';

const emit = defineEmits([
    'create',
    'view',
    'deactivate',
    'activate',
    'delete',
    'import',
]);

type StatusFilter = 'all' | PatientStatusKey;
type SortField = 'name' | 'email' | 'created_at';
type SortDir = 'asc' | 'desc';

const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const gridApi = ref<any>(null);

const rowData = ref<PatientWithInvite[]>([]);
const loading = ref(false);
const page = ref(1);
const perPage = ref(15);
const total = ref(0);
const totalPages = ref(0);
const sortField = ref<SortField>('created_at');
const sortDir = ref<SortDir>('desc');
const activeStatus = ref<StatusFilter>('all');

const statusLabel: Record<StatusFilter, string> = {
    all: 'Todos',
    ...PATIENT_STATUS,
};

const statusFilterOptions: StatusFilter[] = [
    'all',
    ...(Object.keys(PATIENT_STATUS) as PatientStatusKey[]),
];

const fromTo = computed(() => {
    const f = (page.value - 1) * perPage.value + 1;
    const t = Math.min(page.value * perPage.value, total.value);
    return total.value ? `${f}-${t} de ${total.value}` : '0';
});

function onGridReady(params: any) {
    gridApi.value = params.api;
}

async function fetchPatients() {
    if (loading.value) return;
    loading.value = true;
    try {
        const { data } = await axios.get<{
            data: PatientWithInvite[];
            meta: {
                current_page: number;
                last_page: number;
                per_page: number;
                total: number;
                from: number | null;
                to: number | null;
            };
        }>('/patients/table', {
            params: {
                page: page.value,
                per_page: perPage.value,
                sort_field: sortField.value,
                sort_dir: sortDir.value,
                status: activeStatus.value,
            },
        });
        rowData.value = data.data;
        total.value = data.meta.total;
        totalPages.value = data.meta.last_page;
    } catch {
        rowData.value = [];
        total.value = 0;
        totalPages.value = 0;
    } finally {
        loading.value = false;
    }
}

function refetch() {
    return fetchPatients();
}

onMounted(() => {
    fetchPatients();
    if (refreshTableRef) {
        refreshTableRef.value = refetch;
    }
});

watch([page, perPage, sortField, sortDir, activeStatus], () => {
    fetchPatients();
});

function filterByStatus(status: StatusFilter) {
    activeStatus.value = status;
    page.value = 1;
}

function goToPage(p: number) {
    if (p >= 1 && p <= totalPages.value) {
        page.value = p;
    }
}

const columnDefs = [
    {
        headerName: 'Código',
        colId: 'code',
        field: 'code',
        flex: 2,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Nome',
        colId: 'name',
        field: 'name',
        flex: 2,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Email',
        field: 'email',
        flex: 2,
        sortable: true,
        filter: true,
        valueGetter: (params: any) => params.data?.email ?? '—',
    },
    {
        headerName: 'Estudantes',
        colId: 'students',
        flex: 2,
        sortable: false,
        filter: true,
        autoHeight: true,

        valueGetter: (params: any) => {
            const students = params.data?.students ?? [];

            return students
                .map((student: any) => student.name)
                .join(', ');
        },

        cellRenderer: (params: any) => {
            const students = params.data?.students ?? [];

            if (!students.length) {
                return '—';
            }

            return `
                <div class="flex flex-wrap gap-1 py-1">
                    ${students
                        .map(
                            (student: any) => `
                                <span
                                    class="
                                        rounded-full
                                        bg-sky-100
                                        px-2
                                        py-0.5
                                        text-xs
                                        font-medium
                                        text-sky-700
                                    "
                                >
                                    ${student.name}
                                </span>
                            `,
                        )
                        .join('')}
                </div>
            `;
        },
    },
    {
        headerName: 'Status',
        colId: 'status',
        filter: true,
        valueGetter: (params: any) => {
            const patient = params.data;
            if (patient?.deleted_at) return 'Excluído';
            const key = (patient?.status ?? 'ativo') as keyof typeof PATIENT_STATUS;
            return PATIENT_STATUS[key] ?? key;
        },
        cellRenderer: StatusBadge,
    },
    {
        headerName: 'Ações',
        colId: 'actions',
        cellClass: 'cell-center',
        cellRenderer: PatientTableActionsButtons,
        cellRendererParams: {
            onView: (patient: PatientWithInvite) => emit('view', patient),
            onDeactivate: (patient: PatientWithInvite) =>
                emit('deactivate', patient),
            onActivate: (patient: PatientWithInvite) =>
                emit('activate', patient),
            onDelete: (patient: PatientWithInvite) => emit('delete', patient),
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
                    Pacientes
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie o cadastro de pacientes
                </p>
            </div>

            <div class="flex gap-2">
                <ImportExcelButton @click="$emit('import')" />

                <CreateButton
                    label="Novo Paciente"
                    icon="Plus"
                    class="w-full sm:w-auto"
                    @click="$emit('create')"
                />
            </div>
        </div>
        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative"
                style="height: 500px; width: 100%"
            >
                <div
                    class="mb-4 flex flex-wrap gap-1 rounded-full bg-gray-100 p-1"
                >
                    <button
                        v-for="s in statusFilterOptions"
                        :key="s"
                        @click="filterByStatus(s)"
                        class="rounded-full px-3 py-1.5 text-sm font-medium transition-all cursor-pointer"
                        :class="
                            activeStatus === s
                                ? 'bg-white text-gray-900 shadow'
                                : 'text-gray-500 hover:text-gray-900'
                        "
                    >
                        {{ statusLabel[s] }}
                    </button>
                </div>

                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
                >
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span
                            class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                        ></span>
                        Carregando pacientes
                    </div>
                </div>
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="rowData"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :components="{ PatientTableActionsButtons }"
                    @grid-ready="onGridReady"
                    :localeText="AG_GRID_LOCALE_BR"
                />
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
                    :disabled="page <= 1"
                    @click="goToPage(page - 1)"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                >
                    Anterior
                </button>
                <template v-for="p in totalPages" :key="p">
                    <button
                        v-if="
                            p === 1 ||
                            p === totalPages ||
                            (p >= page - 2 && p <= page + 2)
                        "
                        type="button"
                        @click="goToPage(p)"
                        :class="[
                            'rounded-md px-3 py-1.5 text-sm transition',
                            p === page
                                ? 'bg-sky-600 text-white shadow'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
                    >
                        {{ p }}
                    </button>
                    <span
                        v-else-if="p === page - 3 || p === page + 3"
                        class="px-1"
                    >
                        …
                    </span>
                </template>
                <button
                    type="button"
                    :disabled="page >= totalPages"
                    @click="goToPage(page + 1)"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>
