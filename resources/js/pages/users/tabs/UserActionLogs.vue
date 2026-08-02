<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Histórico de ações
                </h2>

                <p class="text-sm text-gray-500">
                    Registro das ações realizadas pelo usuário no sistema
                </p>
            </div>
        </div>

        <UserActionLogsFilters />

        <div v-if="logs.data.length" class="overflow-x-auto">
            <table
                class="w-full table-fixed overflow-hidden rounded-xl border text-sm"
            >
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="w-44 p-3 text-left">
                            Data
                        </th>

                        <th class="w-48 p-3 text-left">
                            Módulo
                        </th>

                        <th class="w-48 p-3 text-left">
                            Realizado por
                        </th>

                        <th class="w-32 p-3 text-left">
                            Ação
                        </th>

                        <th class="p-3 text-left">
                            Descrição
                        </th>

                        <th class="w-12 p-3"></th>
                    </tr>
                </thead>

                <tbody>
                    <template
                        v-for="log in logs.data"
                        :key="log.id"
                    >
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3">
                                {{ formatDateBr(log.created_at) }}
                            </td>

                            <td class="p-3">
                                {{ log.module }}
                            </td>

                            <td class="p-3">
                                {{ log.user?.name ?? 'Usuário não encontrado' }}
                            </td>

                            <td class="p-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="getActionColor(log.action)"
                                >
                                    {{ getActionLabel(log.action) }}
                                </span>
                            </td>

                            <td class="p-3">
                                {{ log.description }}
                            </td>

                            <td class="w-12 p-3 text-center">
                                <button
                                    type="button"
                                    class="rounded p-1 hover:bg-gray-100"
                                    @click="toggleLog(log.id)"
                                >
                                    <ChevronDown
                                        class="h-4 w-4 transition-transform duration-200 ease-in-out"
                                        :class="{ 'rotate-180': isExpanded(log.id) }"
                                    />
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5" class="bg-gray-50 p-0 border-t-0">
                                <Transition
                                    enter-active-class="transition-all duration-300 ease-in-out"
                                    leave-active-class="transition-all duration-300 ease-in-out"
                                    enter-from-class="max-h-0 opacity-0"
                                    enter-to-class="max-h-[800px] opacity-100"
                                    leave-from-class="max-h-[800px] opacity-100"
                                    leave-to-class="max-h-0 opacity-0"
                                >
                                    <div
                                        v-if="isExpanded(log.id)"
                                        class="overflow-hidden px-4 py-4"
                                    >
                                        <div v-if="log.changes">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b text-left text-gray-500">
                                                        <th class="pb-2">
                                                            {{ $t('userActionLogs.headers.field') }}
                                                        </th>

                                                        <th class="pb-2">
                                                            {{ $t('userActionLogs.headers.before') }}
                                                        </th>

                                                        <th class="pb-2"> 
                                                            {{ $t('userActionLogs.headers.after') }}
                                                        </th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <tr
                                                        v-for="[field, change] in visibleChanges(log.changes)"
                                                        :key="field"
                                                        class="border-b last:border-b-0"
                                                    >
                                                        <td class="py-2 font-medium">
                                                            {{ getFieldLabel(String(field)) }}
                                                        </td>

                                                        <td class="py-2">
                                                            {{ formatFieldValue(String(field), change.old) }}
                                                        </td>

                                                        <td class="py-2">
                                                            {{ formatFieldValue(String(field), change.new) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <p
                                            v-else
                                            class="text-sm text-gray-500"
                                        >
                                            {{ $t('userActionLogs.noDetails') }}
                                        </p>
                                    </div>
                                </Transition>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>


        <p
            v-else
            class="text-sm text-gray-400"
        >
            Nenhuma ação registrada.
        </p>

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
                    @click="changePage(page - 1)"
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
                        @click="changePage(p)"
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
                    @click="changePage(page + 1)"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { UserActionLogsContextKey } from '@/keys/action-logs/userActionLogsKeys';
import { inject } from 'vue';
import UserActionLogsFilters from '../components/logs/UserActionLogsFilters.vue';
import { ChevronDown } from 'lucide-vue-next';
import { useUserActionLogTranslations } from '@/composables/user/useUserActionLogTranslations';
import { formatDateBr } from '@/src/utils/formatters.js';

const context = inject(
    UserActionLogsContextKey,
);

if (!context) {
    throw new Error(
        'UserActionLogsContext não encontrado',
    );
}

const {
    logs,
    page,
    totalPages,
    fromTo,
    toggleLog,
    isExpanded,
    changePage,
} = context;

const {
    getActionLabel,
    getActionColor,
    getFieldLabel,
    formatFieldValue,
    visibleChanges
} = useUserActionLogTranslations();
</script>