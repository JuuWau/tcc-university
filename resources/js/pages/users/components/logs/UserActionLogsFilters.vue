<script setup lang="ts">
import { computed, inject, onMounted } from 'vue';
import { X } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { UserActionLogsContextKey, } from '@/keys/action-logs/userActionLogsKeys';

const context = inject(UserActionLogsContextKey);

if (!context) {
    throw new Error(
        'UserActionLogsContext não encontrado.',
    );
}
const {
    filters,
    modules,
    actions,
    search,
    clearFilters,
    loadFilters,
} = context;

const logTypes = ['all', 'performed', 'received'] as const;

type LogType = (typeof logTypes)[number];

const typeLabels: Record<LogType, string> = {
    all: 'Todos',
    performed: 'Realizados',
    received: 'Recebidos',
};

function changeType(type: LogType) {
    filters.value.type = type;
    search();
}

onMounted(() => {
    void loadFilters();
});

const moduleOptions = computed(() =>
    modules.value.map((module) => ({
        label: module,
        value: module,
    })),
);

const actionLabels: Record<string, string> = {
    create: 'Cadastro',
    update: 'Edição',
    delete: 'Exclusão',
};

const actionOptions = computed(() =>
    actions.value.map((action) => ({
        label: actionLabels[action] ?? action,
        value: action,
    })),
);
</script>

<template>
    <div
        class="mb-6 grid items-end gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-6"
    >
        <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Busca
            </label>

            <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar descrição..."
                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                @input="search"
            />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Módulo
            </label>

            <AppMultiselect
                v-model="filters.module"
                :options="moduleOptions"
                label="label"
                value-prop="value"
                placeholder="Todos os módulos"
                :searchable="true"
                :can-clear="true"
                :append-to-body="true"
                @update:modelValue="search"
            />
        </div>


        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Ação
            </label>

            <AppMultiselect
                v-model="filters.action"
                :options="actionOptions"
                label="label"
                value-prop="value"
                placeholder="Todas as ações"
                :searchable="true"
                :can-clear="true"
                :append-to-body="true"
                @update:modelValue="search"
            />
        </div>


        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Data
            </label>

            <input
                v-model="filters.date"
                type="date"
                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                @input="search"
            />
        </div>


        <div class="flex h-full items-end justify-start">
            <Button
                variant="outline"
                class="flex w-full cursor-pointer items-center gap-2"
                @click="clearFilters"
            >
                <X class="h-4 w-4" />
                Limpar
            </Button>
        </div>
    </div>

    <div class="mb-4 inline-flex rounded-full bg-gray-100 p-1">
        <button
            v-for="type in ['all', 'performed', 'received']"
            :key="type"
            @click="changeType(type)"
            class="relative cursor-pointer rounded-full px-4 py-1.5 text-sm font-medium transition-all"
            :class="
                filters.type === type
                    ? 'bg-white text-gray-900 shadow'
                    : 'text-gray-500 hover:text-gray-900'
            "
        >
            {{ typeLabels[type] }}
        </button>
    </div>
</template>