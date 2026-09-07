<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import Button from '@/components/ui/button/Button.vue';
import { UserActionLogsContextKey } from '@/keys/action-logs/userActionLogsKeys';
import { X } from 'lucide-vue-next';
import { computed, inject, onMounted } from 'vue';

const context = inject(UserActionLogsContextKey);

if (!context) {
    throw new Error('UserActionLogsContext não encontrado.');
}
const { filters, modules, actions, search, clearFilters, loadFilters } =
    context;

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
            <BaseInput
                v-model="filters.search"
                label="Busca"
                type="text"
                placeholder="Buscar descrição..."
                @input="search"
            />
        </div>
        <div>
            <AppMultiselect
                v-model="filters.module"
                :options="moduleOptions"
                field-label="Módulo"
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
            <AppMultiselect
                v-model="filters.action"
                :options="actionOptions"
                field-label="Ação"
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
            <BaseInput
                v-model="filters.date"
                label="Data"
                type="date"
                @input="search"
            />
        </div>
        <div class="flex h-full items-end">
            <Button
                variant="outline"
                class="inline-flex h-9 w-full cursor-pointer items-center justify-center gap-2 rounded-lg border-gray-200 bg-white text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 active:scale-[0.98]"
                @click="clearFilters"
            >
                <X class="h-4 w-4" /> Limpar
            </Button>
        </div>
    </div>
    <div class="mb-4 inline-flex rounded-full bg-gray-100 p-1">
        <button
            v-for="type in ['all', 'performed', 'received']"
            :key="type"
            type="button"
            class="relative cursor-pointer rounded-full px-4 py-1.5 text-sm font-medium transition-all"
            :class="
                filters.type === type
                    ? 'bg-white text-gray-900 shadow'
                    : 'text-gray-500 hover:text-gray-900'
            "
            @click="changeType(type)"
        >
            {{ typeLabels[type] }}
        </button>
    </div>
</template>
