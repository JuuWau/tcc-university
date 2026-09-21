<script setup lang="ts">
import PrePatientStatusBadge from '@/components/badges/PrePatientStatusBadge.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import PrePatientTableActionsButtons from '@/components/buttons/PrePatientTableActionsButtons.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { usePrePatients } from '@/composables/pre-patients/usePrePatients';
import { RefreshTableKey } from '@/keys/pre-patients/prePatientKeys';
import { formatDateTimeBr } from '@/src/utils/formatters.js';
import { PrePatient } from '@/types/pre-patients/prePatient.js';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { AgGridVue } from 'ag-grid-vue3';
import { Search } from 'lucide-vue-next';
import {
    computed,
    inject,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import PrePatientCard from './components/PrePatientCard.vue';

const emit = defineEmits(['create', 'edit', 'delete', 'convert']);

type StatusFilter = 'all' | 'waiting' | 'converted' | 'cancelled';

const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);

const {
    loading,
    prePatients,
    search,
    activeStatus,
    page,
    perPage,
    total,
    totalPages,
    loadPrePatients,
    setSearch,
    setStatus,
    goToPage,
} = usePrePatients();

const statusContainer = ref<HTMLElement | null>(null);

const hasHorizontalScroll = ref(false);

function checkHorizontalScroll() {
    if (!statusContainer.value) return;

    hasHorizontalScroll.value =
        statusContainer.value.scrollWidth > statusContainer.value.clientWidth;
}

const statusLabel: Record<StatusFilter, string> = {
    all: 'Todos',
    waiting: 'Aguardando',
    converted: 'Convertido',
    cancelled: 'Cancelado',
};

const fromTo = computed(() => {
    const from = (page.value - 1) * perPage.value + 1;

    const to = Math.min(page.value * perPage.value, total.value);

    return total.value ? `${from}-${to} de ${total.value}` : '0';
});

let searchTimeout: number;

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = window.setTimeout(() => {
        page.value = 1;
        loadPrePatients();
    }, 400);
});

watch(
    [page, perPage, activeStatus],
    () => {
        loadPrePatients();
    },
    {
        deep: false,
    },
);

onMounted(() => {
    loadPrePatients();

    if (refreshTableRef) {
        refreshTableRef.value = refetch;
    }

    nextTick(() => {
        checkHorizontalScroll();
    });

    window.addEventListener('resize', checkHorizontalScroll);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkHorizontalScroll);

    clearTimeout(searchTimeout);
});

function refetch() {
    return loadPrePatients();
}

function filterByStatus(status: StatusFilter) {
    if (activeStatus.value === status) return;

    setStatus(status === 'all' ? null : status);
}

function onGridReady(params: any) {
    params.api;
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
        headerName: 'Telefone',
        field: 'phone',
        flex: 1,
        sortable: false,
        filter: false,
    },
    {
        headerName: 'Clínicas',
        colId: 'clinics',
        flex: 2,
        sortable: false,
        filter: false,
        valueGetter: (params: any) => {
            const clinics = params.data?.clinics;

            if (!clinics?.length) {
                return '';
            }

            return clinics
                .map((clinic: { name: string }) => clinic.name)
                .join(', ');
        },
    },
    {
        headerName: 'Cadastro',
        field: 'created_at',
        flex: 1,
        sortable: false,
        filter: false,
        valueFormatter: (params) => formatDateTimeBr(params.value),
    },
    {
        headerName: 'Status',
        colId: 'status',
        flex: 1,
        filter: false,
        valueGetter: (params: any) => {
            return params.data?.status;
        },
        cellRenderer: PrePatientStatusBadge,
    },
    {
        headerName: 'Ações',
        colId: 'actions',
        width: 100,
        minWidth: 100,
        maxWidth: 100,
        sortable: false,
        filter: false,
        cellRenderer: PrePatientTableActionsButtons,
        cellRendererParams: {
            canDelete: true,
            onEdit: (prePatient: PrePatient) => emit('edit', prePatient),
            onDelete: (prePatient: PrePatient) => emit('delete', prePatient),
            onConvert: (prePatient: PrePatient) => emit('convert', prePatient),
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
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Pré-Pacientes
                </h1>

                <p class="text-sm text-gray-500">
                    Gerencie os pré-pacientes e suas listas de espera
                </p>
            </div>

            <CreateButton
                label="Novo Pré-Paciente"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>

        <div class="mb-4 gap-3">
            <div class="pb-4">
                <div class="relative">
                    <BaseInput
                        v-model="search"
                        type="text"
                        placeholder="Pesquisar por nome ou CPF..."
                        :icon="Search"
                    />
                </div>
            </div>

            <div class="relative mb-4">
                <div
                    ref="statusContainer"
                    class="flex w-full scrollbar-none overflow-x-auto rounded-full bg-gray-100 p-1 pr-8 sm:inline-flex sm:w-auto sm:pr-1"
                >
                    <button
                        v-for="status in [
                            'all',
                            'waiting',
                            'converted',
                            'cancelled',
                        ] as StatusFilter[]"
                        :key="status"
                        type="button"
                        @click="filterByStatus(status)"
                        class="relative shrink-0 rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition-all"
                        :class="
                            activeStatus === (status === 'all' ? null : status)
                                ? 'bg-white text-gray-900 shadow'
                                : 'text-gray-500 hover:text-gray-900'
                        "
                    >
                        {{ statusLabel[status] }}
                    </button>
                </div>

                <div
                    v-if="hasHorizontalScroll"
                    class="pointer-events-none absolute top-0 right-0 flex h-full items-center bg-gradient-to-l from-white via-white/80 to-transparent pl-5 sm:hidden"
                >
                    <span class="pr-2 text-lg text-gray-400"> → </span>
                </div>
            </div>

            <div
                class="ag-theme-alpine relative hidden md:block"
                style="height: 500px; width: 100%"
            >
                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
                >
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span
                            class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                        ></span>

                        Carregando pré-pacientes
                    </div>
                </div>

                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="prePatients"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    @grid-ready="onGridReady"
                    :localeText="AG_GRID_LOCALE_BR"
                />
            </div>

            <div class="space-y-3 md:hidden">
                <div
                    v-if="loading"
                    class="flex items-center justify-center py-10 text-sm text-gray-600"
                >
                    <span
                        class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                    ></span>

                    Carregando pré-pacientes
                </div>

                <div
                    v-else-if="prePatients.length === 0"
                    class="py-10 text-center text-sm text-gray-500"
                >
                    Nenhum pré-paciente encontrado.
                </div>

                <template v-else>
                    <PrePatientCard
                        v-for="prePatient in prePatients"
                        :key="prePatient.id"
                        :pre-patient="prePatient"
                        @edit="emit('edit', $event)"
                        @delete="emit('delete', $event)"
                    />
                </template>
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
