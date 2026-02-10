<script setup lang="ts">
import StatusBadge from '@/components/badges/StatusBadge.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import StudentTableActionsButtons from '@/components/buttons/StudentTableActionsButtons.vue';
import { RefreshTableKey } from '@/keys/students/studentKeys';
import { Student } from '@/types/student/student';
import { AgGridVue } from 'ag-grid-vue3';
import axios from 'axios';
import { computed, inject, onMounted, ref, watch } from 'vue';

const emit = defineEmits([
    'create',
    'edit',
    'delete',
    'view',
    'activate',
    'deactivate',
    'resend',
]);

type StatusFilter = 'all' | 'pending' | 'active' | 'inactive';
type SortField = 'person.name' | 'registration' | 'created_at';
type SortDir = 'asc' | 'desc';

const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const gridApi = ref<any>(null);

const rowData = ref<Student[]>([]);
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
    pending: 'Pendente',
    active: 'Ativo',
    inactive: 'Inativo',
};

const fromTo = computed(() => {
    const f = (page.value - 1) * perPage.value + 1;
    const t = Math.min(page.value * perPage.value, total.value);
    return total.value ? `${f}-${t} de ${total.value}` : '0';
});

function onGridReady(params: any) {
    gridApi.value = params.api;
}

async function fetchStudents() {
    if (loading.value) return;
    loading.value = true;
    try {
        const { data } = await axios.get<{
            data: Student[];
            meta: {
                current_page: number;
                last_page: number;
                per_page: number;
                total: number;
                from: number | null;
                to: number | null;
            };
        }>('/students/table', {
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
    } finally {
        loading.value = false;
    }
}

function refetch() {
    return fetchStudents();
}

onMounted(() => {
    fetchStudents();
    if (refreshTableRef) {
        refreshTableRef.value = refetch;
    }
});

watch([page, perPage, sortField, sortDir, activeStatus], () => {
    fetchStudents();
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
        headerName: 'Nome',
        field: 'person.name',
        flex: 2,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Registro Acadêmico',
        field: 'registration',
        flex: 1,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Período',
        colId: 'period',
        sortable: true,
        filter: true,
        valueGetter: (params: any) => {
            const periods = params.data?.periods;
            if (!periods?.length) return '';
            const current =
                periods.find((p: any) => p.pivot?.is_current) || periods[0];
            return `${current.academic_year}º ano ${current.semester}º semestre ${current.calendar_year}`;
        },
    },
    {
        headerName: 'Status',
        colId: 'status',
        filter: true,
        valueGetter: (params: any) => {
            const student = params.data;
            if (student?.deleted_at) return 'Inativo';
            const invite = student?.user?.invite;
            if (!invite) return 'Ativo';
            if (invite.used_at) return 'Ativo';
            return 'Pendente';
        },
        cellRenderer: StatusBadge,
    },
    {
        headerName: 'Ações',
        colId: 'actions',
        cellClass: 'cell-center',
        cellRenderer: StudentTableActionsButtons,
        cellRendererParams: {
            onView: (student: Student) => emit('edit', student),
            onDeactivate: (student: Student) => emit('deactivate', student),
            onActivate: (student: Student) => emit('activate', student),
            onResend: (student: Student) => emit('resend', student),
            onDelete: (student: Student) => emit('delete', student),
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};

function onAction(payload: { action: string; student: Student }) {
    const { action, student } = payload;
    if (action === 'view') emit('edit', student);
    if (action === 'deactivate') emit('deactivate', student);
    if (action === 'activate') emit('activate', student);
    if (action === 'resend') emit('resend', student);
    if (action === 'delete') emit('delete', student);
}
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Estudantes
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie alunos, status e convites
                </p>
            </div>

            <CreateButton
                label="Novo Estudante"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>
        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative"
                style="height: 500px; width: 100%"
            >
                <div class="mb-4 inline-flex rounded-full bg-gray-100 p-1">
                    <button
                        v-for="s in [
                            'all',
                            'pending',
                            'active',
                            'inactive',
                        ] as StatusFilter[]"
                        :key="s"
                        @click="filterByStatus(s)"
                        class="relative rounded-full px-4 py-1.5 text-sm font-medium transition-all"
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
                        Carregando alunos
                    </div>
                </div>
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="rowData"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :context="{ emit: onAction }"
                    :components="{ StudentTableActionsButtons }"
                    @grid-ready="onGridReady"
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
