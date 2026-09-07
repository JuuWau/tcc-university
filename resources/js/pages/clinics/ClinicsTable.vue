<script setup lang="ts">
import axios from 'axios';
import ClinicTableActionsButtons from '@/components/buttons/ClinicTableActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { RefreshTableKey } from '@/keys/clinics/clinicKeys';
import type { Clinic, Specialty } from '@/types/clinic/clinic';
import { AgGridVue } from 'ag-grid-vue3';
import { computed, inject, onMounted, ref, watch } from 'vue';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { Search } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import ClinicCard from './components/ClinicCard.vue';

const page = usePage();

const can = (permission: string) => {
    return page.props.auth.permissions.includes(permission);
};

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', clinic: Clinic): void;
    (e: 'deactivate', clinic: Clinic): void;
    (e: 'activate', clinic: Clinic): void;
    (e: 'delete', clinic: Clinic): void;
}>();

type StatusFilter = 'all' | 'active' | 'inactive';
type SortField = 'name' | 'created_at';
type SortDir = 'asc' | 'desc';
const activeStatus = ref<StatusFilter>('all');
const refreshTableRef = inject(RefreshTableKey);
const rowData = ref<Clinic[]>([]);
const loading = ref(false);
const pageNumber = ref(1);
const perPage = ref(15);
const total = ref(0);
const totalPages = ref(0);
const sortField = ref<SortField>('name');
const sortDir = ref<SortDir>('asc');
const search = ref('');
let searchTimeout: number;

const statusLabel: Record<StatusFilter, string> = {
    all: 'Todos',
    active: 'Ativas',
    inactive: 'Inativas',
};

const fromTo = computed(() => {
    const from = (pageNumber.value - 1) * perPage.value + 1;
    const to = Math.min(pageNumber.value * perPage.value, total.value);
    return total.value ? `${from}-${to} de ${total.value}` : '0';
});

async function fetchClinics() {
    loading.value = true;
    try {
        const { data } = await axios.get<{
            data: Clinic[];
            meta: { last_page: number; total: number };
        }>('/clinics/table', {
            params: {
                page: pageNumber.value,
                per_page: perPage.value,
                sort_field: sortField.value,
                sort_dir: sortDir.value,
                status: activeStatus.value,
                search: search.value,
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

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => {
        pageNumber.value = 1;
        fetchClinics();
    }, 400);
});

watch([pageNumber, perPage, activeStatus, sortField, sortDir], fetchClinics);

onMounted(() => {
    fetchClinics();
    if (refreshTableRef) refreshTableRef.value = fetchClinics;
});

function goToPage(newPage: number) {
    if (newPage >= 1 && newPage <= totalPages.value) pageNumber.value = newPage;
}

const columnDefs = [
    {
        headerName: 'Nome',
        field: 'name',
        flex: 2,
        sortable: false,
        filter: false,
    },
    {
        headerName: 'Especialidades',
        field: 'specialties',
        flex: 2,
        sortable: false,
        filter: false,
        autoHeight: true,
        cellClass: 'cell-center',
        cellRenderer: (params: any) => {
            if (!params.value?.length) return '';

            return `
                <div class="flex flex-wrap items-center h-full gap-1">
                    ${params.value
                        .map(
                            (specialty: Specialty) => `
                                <span class="px-2 py-0.5 text-xs font-medium rounded-xl bg-sky-100 text-sky-700">
                                    ${specialty.name}
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
        field: 'active',
        sortable: false,
        filter: false,
        cellRenderer: (params: any) => {
            const active = !!params.value;
            const bgClass = active
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-gray-100 text-gray-600';
            const label = active ? 'Ativa' : 'Inativa';
            return `<span class="rounded px-2 py-1 text-xs font-medium ${bgClass}">${label}</span>`;
        },
    },
    {
        headerName: 'Ações',
        field: 'id',
        cellRenderer: ClinicTableActionsButtons,
        cellRendererParams: {
            onEdit: (clinic: Clinic) => emit('edit', clinic),
            onDeactivate: (clinic: Clinic) => emit('deactivate', clinic),
            onActivate: (clinic: Clinic) => emit('activate', clinic),
            onDelete: (clinic: Clinic) => emit('delete', clinic),
            canDelete: can('clinics.delete'),
            canDeactivate: can('clinics.deactivate')
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};

function filterByStatus(status: StatusFilter) {
    activeStatus.value = status;
    pageNumber.value = 1;
}
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Clínicas
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie clínicas/laboratórios para abertura de agenda
                </p>

                <div class="mt-3 inline-flex rounded-full bg-gray-100 p-1">
                    <button
                        v-for="s in [
                            'all',
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
            </div>
            <CreateButton
                label="Nova Clínica"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>

        <div class="mb-4">
            <BaseInput
                v-model="search"
                type="text"
                placeholder="Pesquisar clínica..."
                :icon="Search"
            />
        </div>

        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative hidden rounded-xl border border-gray-200 md:block"
                style="height: 520px; width: 100%"
            >
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="rowData"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :localeText="AG_GRID_LOCALE_BR"
                />

                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70"
                >
                    <span
                        class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                    ></span>
                    Carregando clínicas
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                <div
                    v-if="loading"
                    class="flex items-center justify-center py-10 text-sm text-gray-600"
                >
                    <span
                        class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                    ></span>
                    Carregando clínicas
                </div>

                <ClinicCard
                    v-for="clinic in rowData"
                    :key="clinic.id"
                    :clinic="clinic"
                    :can-delete="can('clinics.delete')"
                    :can-deactivate="can('clinics.deactivate')"
                    @edit="emit('edit', $event)"
                    @deactivate="emit('deactivate', $event)"
                    @activate="emit('activate', $event)"
                    @delete="emit('delete', $event)"
                />

                <div
                    v-if="!loading && rowData.length === 0"
                    class="rounded-xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500"
                >
                    Nenhuma clínica encontrada.
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
