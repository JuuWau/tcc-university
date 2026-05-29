<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Agendamentos
                </h2>
                <p class="text-sm text-gray-500">
                    Agendamentos próximos do paciente
                </p>
            </div>
        </div>

        <div class="space-y-8">
            <!-- ===================== FUTUROS ===================== -->
            <div>
                <h2 class="mb-3 text-lg font-semibold text-gray-700">
                    Próximos Agendamentos
                </h2>

                <div v-if="upcoming.length" class="overflow-x-auto">
                    <table
                        class="w-full overflow-hidden rounded-xl border text-sm"
                    >
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Início</th>
                                <th class="p-3 text-left">Término</th>
                                <th class="p-3 text-left">Clínica</th>
                                <th class="p-3 text-left">Procedimento</th>
                                <th class="p-3 text-left">Dentista</th>
                                <th class="p-3 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="item in upcoming"
                                :key="item.id"
                                @click="openModal(item)"
                                class="cursor-pointer border-t hover:bg-gray-50"
                            >
                                <td class="p-3">{{ item.start }}</td>
                                <td class="p-3">{{ item.end }}</td>
                                <td class="p-3">{{ item.clinic }}</td>
                                <td class="p-3">{{ item.procedure }}</td>
                                <td class="p-3">{{ item.dentist }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="getStatusColor(item.status)"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-else class="text-sm text-gray-400">
                    Nenhum agendamento futuro.
                </p>
            </div>
        </div>
    </div>
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Agendamentos realizados
                </h2>
                <p class="text-sm text-gray-500">
                    Agendamentos finalizados do paciente
                </p>
            </div>
        </div>

        <!-- ===================== PASSADOS ===================== -->
        <div>
            <h2 class="mb-3 text-lg font-semibold text-gray-700">
                Histórico de Atendimentos
            </h2>

            <div v-if="past.length" class="overflow-x-auto">
                <table class="w-full overflow-hidden rounded-xl border text-sm">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-3 text-left">Início</th>
                            <th class="p-3 text-left">Término</th>
                            <th class="p-3 text-left">Clínica</th>
                            <th class="p-3 text-left">Procedimento</th>
                            <th class="p-3 text-left">Dentista</th>
                            <th class="p-3 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="item in past"
                            :key="item.id"
                            @click="openModal(item)"
                            class="cursor-pointer border-t hover:bg-gray-50"
                        >
                            <td class="p-3">{{ item.start }}</td>
                            <td class="p-3">{{ item.end }}</td>
                            <td class="p-3">{{ item.clinic }}</td>
                            <td class="p-3">{{ item.procedure }}</td>
                            <td class="p-3">{{ item.dentist }}</td>
                            <td class="p-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="getStatusColor(item.status)"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-else class="text-sm text-gray-400">
                Nenhum atendimento realizado.
            </p>
        </div>

        <!-- ===================== MODAL ===================== -->
        <div
            v-if="selected"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
            <div class="w-[500px] space-y-4 rounded-2xl bg-white p-6">
                <h2 class="text-lg font-semibold">Detalhes do Agendamento</h2>

                <!-- Paciente -->
                <div class="space-y-1 text-sm">
                    <p><strong>Código:</strong> {{ selected.patientCode }}</p>
                    <p><strong>Nome:</strong> {{ selected.patientName }}</p>
                    <p><strong>Telefone:</strong> {{ selected.phone }}</p>
                </div>

                <!-- Procedimento -->
                <div class="space-y-1 text-sm">
                    <p>
                        <strong>Procedimento:</strong>
                        {{ selected.procedure }}
                    </p>
                    <p><strong>Área:</strong> {{ selected.area }}</p>
                </div>

                <!-- Horários -->
                <div class="space-y-1 text-sm">
                    <p><strong>Data:</strong> {{ selected.date }}</p>
                    <p><strong>Início:</strong> {{ selected.start }}</p>
                    <p><strong>Fim:</strong> {{ selected.end }}</p>
                    <p><strong>Dentista:</strong> {{ selected.dentist }}</p>
                </div>

                <!-- STATUS (SOMENTE LEITURA SE FOR PASSADO) -->
                <div>
                    <label class="text-sm font-medium">Status</label>

                    <select
                        v-if="!isPast(selected)"
                        v-model="selected.status"
                        class="mt-1 w-full rounded-lg border p-2"
                    >
                        <option v-for="s in statusList" :key="s">
                            {{ s }}
                        </option>
                    </select>

                    <p
                        v-else
                        class="mt-1 text-sm font-medium"
                        :class="getStatusColor(selected.status)"
                    >
                        {{ selected.status }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <button
                        @click="closeModal"
                        class="rounded-lg bg-gray-200 px-4 py-2"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Plus, PlusIcon } from 'lucide-vue-next';
import { ref } from 'vue';

/* ===================== DADOS ===================== */

const upcoming = ref([
    {
        id: 1,
        patientCode: 23623,
        patientName: 'Kauan Felipe De Castro',
        phone: '(43) 99691-8638',
        clinic: 'Ortodontia',
        procedure: 'Remoção de Aparelho',
        area: 'Ortodontia',
        date: '07/04/2026',
        start: '07/05/2026 11:40',
        end: '07/05/2026 12:00',
        dentist: 'Aluno 1',
        status: 'Agendado',
    },
]);

const past = ref([
    {
        id: 2,
        patientCode: 23623,
        patientName: 'Kauan Felipe De Castro',
        phone: '(43) 99691-8638',
        clinic: 'Exodontia',
        procedure: 'Extração',
        area: 'Cirurgia',
        date: '01/03/2026',
        start: '01/03/2026 10:00',
        end: '01/03/2026 10:30',
        dentist: 'Aluno 1',
        status: 'Atendido',
    },
]);

const statusList = [
    'Agendado',
    'Confirmado',
    'Encaixe',
    'Atendido',
    'Remarcado(Paciente)',
    'Remarcado(Aluno)',
    'Não Compareceu',
    'Atendido/Atrasado',
    'Agendado Incorreto',
    'Cancelou',
];

/* ===================== MODAL ===================== */

const selected = ref<any | null>(null);

function openModal(item: any) {
    selected.value = item;
}

function closeModal() {
    selected.value = null;
}

/* ===================== REGRAS ===================== */

function isPast(item: any) {
    return past.value.some((p) => p.id === item.id);
}

function getStatusColor(status: string) {
    switch (status) {
        case 'Agendado':
            return 'bg-blue-100 text-blue-600';
        case 'Confirmado':
            return 'bg-green-100 text-green-600';
        case 'Atendido':
            return 'bg-gray-200 text-gray-700';
        case 'Cancelou':
            return 'bg-red-100 text-red-600';
        case 'Não Compareceu':
            return 'bg-orange-100 text-orange-600';
        default:
            return 'bg-gray-100 text-gray-500';
    }
}
</script>
