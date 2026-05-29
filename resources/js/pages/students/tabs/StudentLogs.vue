<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Histórico de ações</h2>
                <p class="text-sm text-gray-500">
                    Registro de todas as ações realizadas no sistema relacionadas ao aluno
                </p>
            </div>
        </div>
        <div class="space-y-4">

            <div v-if="logs.length" class="space-y-3">
                <div
                    v-for="log in logs"
                    :key="log.id"
                    class="flex items-start gap-3 rounded-xl border p-4"
                >
                    <!-- STATUS ICON -->
                    <div
                        class="mt-1 h-3 w-3 rounded-full"
                        :class="getColor(log.type)"
                    />

                    <div class="flex-1">
                        <p class="text-sm text-gray-800">
                            {{ log.description }}
                        </p>

                        <div class="mt-1 text-xs text-gray-500">
                            {{ log.user_name }} ({{ log.role }}) •
                            {{ formatDate(log.created_at) }}
                        </div>
                    </div>

                    <!-- TAG -->
                    <span
                        class="rounded-full px-2 py-1 text-xs font-medium"
                        :class="getBadge(log.type)"
                    >
                        {{ getLabel(log.type) }}
                    </span>
                </div>
            </div>

            <p v-else class="text-sm text-gray-500">Nenhum log encontrado</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const logs = ref([
    {
        id: 1,
        type: 'appointment',
        description: 'Consulta agendada com Dr. João',
        user_name: 'Julia',
        role: 'Admin',
        created_at: '2026-04-06 14:00',
    },
    {
        id: 2,
        type: 'update',
        description: 'Telefone alterado',
        user_name: 'Vinicius',
        role: 'Staff',
        created_at: '2026-04-05 10:30',
    },
]);

function getColor(type: string) {
    return type === 'appointment' ? 'bg-blue-500' : 'bg-yellow-500';
}

function getBadge(type: string) {
    return type === 'appointment'
        ? 'bg-blue-100 text-blue-700'
        : 'bg-yellow-100 text-yellow-700';
}

function getLabel(type: string) {
    return type === 'appointment' ? 'Agendamento' : 'Alteração';
}

function formatDate(date: string) {
    return new Date(date).toLocaleString('pt-BR');
}
</script>
