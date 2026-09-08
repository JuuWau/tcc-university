<script setup lang="ts">
import axios from 'axios';
import { computed, inject, onMounted, ref, watch } from 'vue';
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { RefreshTableKey } from '@/keys/procedures/procedureKeys';
import type { Procedure } from '@/types/procedure';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { Search } from 'lucide-vue-next';
import ProcedureCard from './components/ProcedureCard.vue';

const emit = defineEmits<{
    (e: 'edit', procedure: Procedure): void;
    (e: 'delete', procedure: Procedure): void;
    (e: 'create'): void;
}>();

type SortField = 'name' | 'created_at';
type SortDir = 'asc' | 'desc';

const refreshTableRef = inject(RefreshTableKey);
const rowData = ref<Procedure[]>([]);
const loading = ref(false);
const page = ref(1);
const perPage = ref(15);
const total = ref(0);
const totalPages = ref(0);
const sortField = ref<SortField>('name');
const sortDir = ref<SortDir>('asc');
const search = ref('');
let searchTimeout: number;

const fromTo = computed(() => {
    const from = (page.value - 1) * perPage.value + 1;
    const to = Math.min(page.value * perPage.value, total.value);
    return total.value ? `${from}-${to} de ${total.value}` : '0';
});

async function fetchProcedures() {
    loading.value = true;
    try {
        const { data } = await axios.get<{
            data: Procedure[];
            meta: { last_page: number; total: number };
        }>('/procedures/table', {
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
        fetchProcedures();
    }, 400);
});

watch([page, perPage, sortField, sortDir], fetchProcedures);

onMounted(() => {
    fetchProcedures();
    if (refreshTableRef) refreshTableRef.value = fetchProcedures;
});

function goToPage(newPage: number) {
    if (newPage >= 1 && newPage <= totalPages.value) page.value = newPage;
}

const columnDefs = [
    {
        headerName: 'Nome',
        field: 'name',
        flex: 2,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Especialidade',
        field: 'specialty',
        flex: 2,
        sortable: true,
        filter: true,
        valueFormatter: (params: { value?: { name?: string } }) =>
            params.value?.name ?? '—',
    },
    {
        headerName: 'Ações',
        field: 'id',
        flex: 1,
        cellRenderer: ActionsButtons,
        cellRendererParams: {
            onEdit: (procedure: Procedure) => emit('edit', procedure),
            onDelete: (procedure: Procedure) => emit('delete', procedure),
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
                    Procedimentos
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie os procedimentos por especialidade
                </p>
            </div>
            <CreateButton
                label="Novo Procedimento"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>

        <div class="mb-4">
            <BaseInput
                v-model="search"
                type="text"
                placeholder="Pesquisar procedimento..."
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
                    :row-data="rowData"
                    :column-defs="columnDefs"
                    :default-col-def="defaultColDef"
                    :locale-text="AG_GRID_LOCALE_BR"
                />

                <div v-if="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/70">
                    <span class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"></span>
                    Carregando procedimentos
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                <div v-if="loading" class="flex items-center justify-center py-10 text-sm text-gray-600">
                    <span class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"></span>
                    Carregando procedimentos
                </div>

                <ProcedureCard
                    v-for="procedure in rowData"
                    :key="procedure.id"
                    :procedure="procedure"
                    @edit="emit('edit', $event)"
                    @delete="emit('delete', $event)"
                />

                <div v-if="!loading && rowData.length === 0" class="rounded-xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                    Nenhum procedimento encontrado.
                </div>
            </div>
        </div>

        <div v-if="totalPages > 0" class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-gray-600">{{ fromTo }}</p>
            <div class="flex items-center gap-1">
                <button type="button" :disabled="page <= 1" class="rounded border border-gray-300 bg-white px-3 py-1 text-sm disabled:opacity-50" @click="goToPage(page - 1)">Anterior</button>
                <button
                    v-for="currentPage in totalPages"
                    :key="currentPage"
                    type="button"
                    :class="['rounded px-3 py-1 text-sm', currentPage === page ? 'bg-sky-600 text-white' : 'text-gray-600 hover:bg-gray-100']"
                    @click="goToPage(currentPage)"
                >
                    {{ currentPage }}
                </button>
                <button type="button" :disabled="page >= totalPages" class="rounded border border-gray-300 bg-white px-3 py-1 text-sm disabled:opacity-50" @click="goToPage(page + 1)">Próxima</button>
            </div>
        </div>
    </div>
</template>
