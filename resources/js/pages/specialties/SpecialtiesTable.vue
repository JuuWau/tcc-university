<script setup lang="ts">
import axios from 'axios';
import { computed, inject, onMounted, ref, watch } from 'vue';
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { RefreshTableKey } from '@/keys/specialties/specialtyKeys';
import { Specialty } from '@/types/specialty';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { Search } from 'lucide-vue-next';
import SpecialtyCard from './components/SpecialtyCard.vue';

type SortField = 'name' | 'created_at';
type SortDir = 'asc' | 'desc';

const emit = defineEmits<{
    (e: 'edit', specialty: Specialty): void;
    (e: 'delete', specialty: Specialty): void;
    (e: 'create'): void;
}>();

const refreshTableRef = inject(RefreshTableKey);

const rowData = ref<Specialty[]>([]);
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

async function fetchSpecialties() {
    loading.value = true;

    try {
        const { data } = await axios.get<{
            data: Specialty[];
            meta: {
                current_page: number;
                last_page: number;
                per_page: number;
                total: number;
                from: number | null;
                to: number | null;
            };
        }>('/specialties/table', {
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
        fetchSpecialties();
    }, 400);
});

watch(
    [page, perPage, sortField, sortDir],
    fetchSpecialties,
);

onMounted(() => {
    fetchSpecialties();

    if (refreshTableRef) {
        refreshTableRef.value = fetchSpecialties;
    }
});

defineExpose({ fetchSpecialties });

function goToPage(newPage: number) {
    if (newPage >= 1 && newPage <= totalPages.value) {
        page.value = newPage;
    }
}

function openEditModal(specialty: Specialty) {
    emit('edit', specialty);
}

function openDeleteModal(specialty: Specialty) {
    emit('delete', specialty);
}

const columnDefs = [
    {
        headerName: 'Nome',
        colId: 'name',
        field: 'name',
        flex: 2,
        sortable: false,
        filter: false,
    },
    {
        headerName: 'Ações',
        colId: 'actions',
        field: 'id',
        flex: 1,
        sortable: false,
        filter: false,
        cellRenderer: ActionsButtons,
        cellRendererParams: {
            onEdit: (specialty: Specialty) => emit('edit', specialty),
            onDelete: (specialty: Specialty) => emit('delete', specialty),
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
    sortable: false,
    filter: false,
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
                    Especialidades
                </h1>

                <p class="text-sm text-gray-500">
                    Gerencie as especialidades cadastradas no sistema
                </p>
            </div>

            <CreateButton
                label="Nova Especialidade"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>

        <div class="mb-4">
            <BaseInput
                v-model="search"
                type="text"
                placeholder="Pesquisar especialidade..."
                :icon="Search"
            />
        </div>

        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative hidden md:block"
                style="height: 500px; width: 100%"
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
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
                >
                    <div
                        class="flex items-center gap-2 text-sm text-gray-600"
                    >
                        <span
                            class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                        ></span>

                        Carregando especialidades
                    </div>
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

                    Carregando especialidades
                </div>

                <SpecialtyCard
                    v-for="specialty in rowData"
                    :key="specialty.id"
                    :specialty="specialty"
                    @edit="openEditModal"
                    @delete="openDeleteModal"
                />

                <div
                    v-if="!loading && rowData.length === 0"
                    class="rounded-xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500"
                >
                    Nenhuma especialidade encontrada.
                </div>
            </div>
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
                    :disabled="page <= 1"
                    @click="goToPage(page - 1)"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
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
                        v-else-if="
                            p === page - 3 ||
                            p === page + 3
                        "
                        class="px-1 text-gray-500"
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
