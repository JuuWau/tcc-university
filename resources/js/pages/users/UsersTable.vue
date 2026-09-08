<script setup lang="ts">
import StatusBadge from '@/components/badges/StatusBadge.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import UserTableActionsButtons from '@/components/buttons/UserTableActionsButtons.vue';
import { RefreshTableKey } from '@/keys/users/userKeys';
import type { UserWithInvite } from '@/types/user/user';
import { AgGridVue } from 'ag-grid-vue3';
import axios from 'axios';
import { computed, inject, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import UserCard from './components/UserCard.vue';
import { Search } from 'lucide-vue-next';
import BaseInput from '@/components/inputs/BaseInput.vue';

const emit = defineEmits(['create', 'resend', 'view', 'deactivate', 'activate', 'delete']);

type StatusFilter = 'all' | 'pending' | 'active' | 'inactive';
type SortField = 'name' | 'email' | 'created_at';
type SortDir = 'asc' | 'desc';

let searchTimeout: number;

const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const gridApi = ref<any>(null);

const rowData = ref<UserWithInvite[]>([]);
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

const search = ref('');

const hasHorizontalScroll = ref(false);

const statusContainer = ref<HTMLElement | null>(null);

const checkHorizontalScroll = () => {
    if (!statusContainer.value) return;

    hasHorizontalScroll.value =
        statusContainer.value.scrollWidth >
        statusContainer.value.clientWidth;
};

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = window.setTimeout(() => {
        page.value = 1;
        fetchUsers();
    }, 400);
});

onMounted(() => {
    nextTick(() => {
        checkHorizontalScroll();
        window.addEventListener('resize', checkHorizontalScroll);
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkHorizontalScroll);
});

const fromTo = computed(() => {
    const f = (page.value - 1) * perPage.value + 1;
    const t = Math.min(page.value * perPage.value, total.value);
    return total.value ? `${f}-${t} de ${total.value}` : '0';
});

function onGridReady(params: any) {
    gridApi.value = params.api;
}

async function fetchUsers() {
    if (loading.value) return;
    loading.value = true;
    try {
        const { data } = await axios.get<{
            data: UserWithInvite[];
            meta: {
                current_page: number;
                last_page: number;
                per_page: number;
                total: number;
                from: number | null;
                to: number | null;
            };
        }>('/users/table', {
            params: {
                page: page.value,
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
    } finally {
        loading.value = false;
    }
}

function refetch() {
    return fetchUsers();
}

onMounted(() => {
    fetchUsers();
    if (refreshTableRef) {
        refreshTableRef.value = refetch;
    }
});

watch(
    [page, perPage, sortField, sortDir, activeStatus],
    fetchUsers,
    {
        deep: false,
    }
);

function filterByStatus(status: StatusFilter) {
    if (activeStatus.value === status) return;

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
        colId: 'name',
        flex: 2,
        sortable: false,
        filter: false,
        valueGetter: (params: any) =>
            params.data?.person?.name ?? params.data?.email ?? '—',
    },
    {
        headerName: 'Email',
        field: 'email',
        flex: 2,
        sortable: false,
        filter: false,
    },
    {
        headerName: 'Perfil',
        field: 'role.name',
        flex: 1,
        sortable: false,
        filter: false,
    },
    {
        headerName: 'Status',
        colId: 'status',
        filter: false,
        valueGetter: (params: any) => {
            const user = params.data;
            if (user?.deleted_at) return 'Inativo';
            const invite = user?.invite;
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
        cellRenderer: UserTableActionsButtons,
        cellRendererParams: {
            onResend: (user: UserWithInvite) => emit('resend', user),
            onView: (user: UserWithInvite) => emit('view', user),
            onDeactivate: (user: UserWithInvite) => emit('deactivate', user),
            onActivate: (user: UserWithInvite) => emit('activate', user),
            onDelete: (user: UserWithInvite) => emit('delete', user),
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
    filter: false,
    sortable: false,
};

function onAction(payload: { action: string; user: UserWithInvite }) {
    const { action, user } = payload;
    if (action === 'resend') emit('resend', user);
    if (action === 'view') emit('view', user);
    if (action === 'deactivate') emit('deactivate', user);
    if (action === 'activate') emit('activate', user);
    if (action === 'delete') emit('delete', user);
}
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Usuários
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie usuários e convites de acesso
                </p>
            </div>

            <CreateButton
                label="Novo Usuário"
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
                        placeholder="Buscar usuário..."
                        :icon="Search"
                    />
                </div>
            </div>
            <div class="relative">
                <div
                    ref="statusContainer"
                    class="flex w-full overflow-x-auto rounded-full bg-gray-100 p-1 pr-8 scrollbar-none sm:inline-flex sm:w-auto sm:pr-1"
                >
                    <button
                        v-for="s in [
                            'all',
                            'pending',
                            'active',
                            'inactive',
                        ] as StatusFilter[]"
                        :key="s"
                        type="button"
                        @click="filterByStatus(s)"
                        class="relative shrink-0 cursor-pointer rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition-all"
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
                    v-if="hasHorizontalScroll"
                    class="pointer-events-none absolute top-0 right-0 flex h-full items-center bg-gradient-to-l from-white via-white/80 to-transparent pl-5 sm:hidden"
                >
                    <span class="pr-2 text-lg text-gray-400">
                        →
                    </span>
                </div>
            </div>
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
                    :context="{ emit: onAction }"
                    :components="{ UserTableActionsButtons }"
                    @grid-ready="onGridReady"
                    :localeText="AG_GRID_LOCALE_BR"
                />

                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
                >
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span
                            class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"
                        ></span>

                        Carregando usuários
                    </div>
                </div>
            </div>

            <div class="space-y-3 md:hidden">
                <UserCard
                    v-for="user in rowData"
                    :key="user.id"
                    :user="user"
                    :on-resend="(user) => emit('resend', user)"
                    :on-view="(user) => emit('view', user)"
                    :on-deactivate="(user) => emit('deactivate', user)"
                    :on-activate="(user) => emit('activate', user)"
                    :on-delete="(user) => emit('delete', user)"
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
