<script setup lang="ts">
import axios from 'axios';
import { computed, inject, onMounted, ref, watch } from 'vue';
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { RefreshTableKey } from '@/keys/periods/periodKeys';
import { Period } from '@/types/period';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { Search } from 'lucide-vue-next';
import PeriodCard from './components/PeriodCard.vue';

type SortField = 'academic_year' | 'semester' | 'calendar_year' | 'created_at';
type SortDir = 'asc' | 'desc';
const emit = defineEmits<{
    (e: 'edit', period: Period): void;
    (e: 'delete', period: Period): void;
    (e: 'create'): void;
}>();

const refreshTableRef = inject(RefreshTableKey);
const rowData = ref<Period[]>([]);
const loading = ref(false);
const page = ref(1);
const perPage = ref(15);
const total = ref(0);
const totalPages = ref(0);
const sortField = ref<SortField>('calendar_year');
const sortDir = ref<SortDir>('desc');
const search = ref('');
let searchTimeout: number;

const fromTo = computed(() => {
    const from = (page.value - 1) * perPage.value + 1;
    const to = Math.min(page.value * perPage.value, total.value);

    return total.value ? `${from}-${to} de ${total.value}` : '0';
});

async function fetchPeriods() {
    loading.value = true;

    try {
        const { data } = await axios.get<{
            data: Period[];
            meta: {
                last_page: number;
                total: number;
            };
        }>('/periods/table', {
            params: {
                page: page.value,
                per_page: perPage.value,
                sort_field: sortField.value,
                sort_dir: sortDir.value,
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
        page.value = 1;
        fetchPeriods();
    }, 400);
});

watch([page, perPage, sortField, sortDir], fetchPeriods);

onMounted(() => {
    fetchPeriods();

    if (refreshTableRef) {
        refreshTableRef.value = fetchPeriods;
    }
});

function goToPage(newPage: number) {
    if (newPage >= 1 && newPage <= totalPages.value) {
        page.value = newPage;
    }
}

const columnDefs = [
    {
        headerName: 'Ano Acadêmico',
        field: 'academic_year',
        flex: 1,
        sortable: false,
        filter: false,
        valueFormatter: (params: any) => {
            if (!params.value) return '';
            return `${params.value}º ano`;
        },
    },
    {
        headerName: 'Semestre',
        field: 'semester',
        flex: 1,
        sortable: false,
        filter: false,
        valueFormatter: (params: any) => {
            if (!params.value) return '';
            return `${params.value}º semestre`;
        },
    },
    {
        headerName: 'Ano Calendário',
        field: 'calendar_year',
        flex: 1,
        sortable: false,
        filter: false,
        valueFormatter: (params: any) => {
            const value = Number(params.value);
            if (!value) return '';
            return `${value}`;
        },
    },
    {
        headerName: 'Especialidades',
        field: 'specialties',
        autoHeight: true,
        flex: 2,
        cellClass: 'cell-center',
        sortable: false,
        filter: false,
        cellRenderer: (params: any) => {
            if (!params.value?.length) return '';

            return `
            <div class="flex flex-wrap items-center h-full gap-1">
                ${params.value
                    .map(
                        (s: any) => `
                    <span class="px-2 py-0.5 text-xs font-medium
                                rounded-xl
                                bg-sky-100 text-sky-700">
                        ${s.name}
                    </span>
                    `,
                    )
                    .join('')}
            </div>
            `;
        },
    },
    {
        headerName: 'Ações',
        field: 'id',
        cellRenderer: ActionsButtons,
        cellRendererParams: {
            onEdit: (period: Period) => emit('edit', period),
            onDelete: (period: Period) => emit('delete', period),
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
                    Períodos
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie anos acadêmicos, semestres e especialidades
                </p>
            </div>

            <CreateButton
                label="Novo Período"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>

        <div class="mb-4">
            <BaseInput
                v-model="search"
                type="text"
                placeholder="Pesquisar período..."
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
                    Carregando períodos
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
                    Carregando períodos
                </div>

                <PeriodCard
                    v-for="period in rowData"
                    :key="period.id"
                    :period="period"
                    @edit="emit('edit', $event)"
                    @delete="emit('delete', $event)"
                />

                <div
                    v-if="!loading && rowData.length === 0"
                    class="rounded-xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500"
                >
                    Nenhum período encontrado.
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
                    :disabled="page <= 1"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm disabled:opacity-50"
                    @click="goToPage(page - 1)"
                >
                    Anterior
                </button>
                <button
                    v-for="currentPage in totalPages"
                    :key="currentPage"
                    type="button"
                    :class="[
                        'rounded px-3 py-1 text-sm',
                        currentPage === page
                            ? 'bg-sky-600 text-white'
                            : 'text-gray-600 hover:bg-gray-100',
                    ]"
                    @click="goToPage(currentPage)"
                >
                    {{ currentPage }}
                </button>
                <button
                    type="button"
                    :disabled="page >= totalPages"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm disabled:opacity-50"
                    @click="goToPage(page + 1)"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>
