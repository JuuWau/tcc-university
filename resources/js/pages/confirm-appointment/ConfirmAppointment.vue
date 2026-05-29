<template>
    <AppLayout>
        <div
            class="m-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <div
                class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">
                        Confirmar Agendamentos
                    </h1>
                    <p class="text-sm text-gray-500">
                        Filtre e confirme os agendamentos
                    </p>
                </div>
            </div>

            <!-- 🟡 FILTROS -->
            <div
                class="grid grid-cols-1 gap-4 rounded-xl border bg-gray-50 p-4 md:grid-cols-5"
            >
                <div class="">
                    <label
                        for="period_id"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Clínica
                    </label>
                    <Multiselect
                        v-model="filters.clinic"
                        :options="periodOptions"
                        label="label"
                        value-prop="value"
                        placeholder="Período"
                    />
                </div>

                <div class="">
                    <label
                        for="period_id"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Período
                    </label>
                    <Multiselect
                        v-model="filters.period"
                        :options="periodOptions"
                        label="label"
                        value-prop="value"
                        placeholder="Período"
                    />
                </div>

                <div class="">
                    <label
                        for="period_id"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Estudante
                    </label>
                    <Multiselect
                        v-model="filters.student"
                        :options="studentOptions"
                        label="label"
                        value-prop="value"
                        placeholder="Aluno"
                        :searchable="true"
                    />
                </div>

                <div>
                    <label
                        for="date"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Data
                    </label>
                    <input
                        id="date"
                        v-model="filters.date"
                        type="date"
                        class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    />
                </div>

                <div class="flex h-full flex-col justify-end gap-2">
                    <Button
                        class="flex items-center justify-center gap-2"
                        @click="applyFilters"
                    >
                        <Filter class="h-4 w-4" />
                        Filtrar
                    </Button>

                    <Button
                        variant="outline"
                        class="flex w-full items-center justify-center gap-2"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                        Limpar
                    </Button>
                </div>
            </div>

            <!-- 🟢 TABELA -->
            <div class="overflow-x-auto rounded-xl border mt-4  ">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-3 text-left">Ação</th>
                            <th class="p-3 text-left">Data</th>
                            <th class="p-3 text-left">Horário</th>
                            <th class="p-3 text-left">Paciente</th>
                            <th class="p-3 text-left">Clínica</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Contato</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="item in filteredData"
                            :key="item.id"
                            class="border-t hover:bg-gray-50"
                        >
                            <!-- BOTÃO CONFIRMAR -->
                            <td class="p-3">
                                <button
                                    @click="confirm(item)"
                                    class="rounded bg-green-600 px-3 py-1 text-xs text-white"
                                >
                                    Confirmar
                                </button>
                            </td>

                            <td class="p-3">{{ formatDate(item.date) }}</td>

                            <td class="p-3">
                                {{ item.start }} às {{ item.end }}
                            </td>

                            <td class="p-3">{{ item.student }}</td>

                            <td class="p-3">{{ item.clinic }}</td>

                            <!-- STATUS -->
                            <td class="p-3">
                                <span
                                    class="rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-700"
                                >
                                    Não confirmado
                                </span>
                            </td>
                            <td class="p-3">
                                <PhoneOutgoing class="h-4 w-4 text-blue-500" />
                            </td>
                        </tr>

                        <tr v-if="!filteredData.length">
                            <td
                                colspan="6"
                                class="p-4 text-center text-gray-400"
                            >
                                Nenhum resultado encontrado
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Multiselect from '@vueform/multiselect';
import { Filter, PhoneOutgoing, SendHorizontal, X } from 'lucide-vue-next';
import { computed, reactive } from 'vue';

/* 🔵 MOCK FILTROS */
const filters = reactive({
    clinic: null,
    period: null,
    student: null,
    date: '',
});

/* 🟣 OPTIONS */
const clinicOptions = [
    { label: 'Clínica A', value: 1 },
    { label: 'Clínica B', value: 2 },
];

const periodOptions = [
    { label: 'Manhã', value: 1 },
    { label: 'Tarde', value: 2 },
];

const studentOptions = [
    { label: 'Maria', value: 1 },
    { label: 'João', value: 2 },
];

/* 🟢 DADOS MOCK */
const data = [
    {
        id: 1,
        date: '2026-04-10',
        start: '14:00',
        end: '15:00',
        student: 'Maria',
        clinic: 'Clínica A',
        clinic_id: 1,
        period_id: 1,
        student_id: 1,
    },
    {
        id: 2,
        date: '2026-04-10',
        start: '15:00',
        end: '16:00',
        student: 'João',
        clinic: 'Clínica B',
        clinic_id: 2,
        period_id: 2,
        student_id: 2,
    },
];

/* 🧠 FILTRO */
const filteredData = computed(() => {
    return data.filter((item) => {
        return (
            (!filters.clinic || item.clinic_id === filters.clinic) &&
            (!filters.period || item.period_id === filters.period) &&
            (!filters.student || item.student_id === filters.student) &&
            (!filters.date || item.date === filters.date)
        );
    });
});

/* 🔧 FUNÇÕES */
function applyFilters() {
    // aqui depois vira API
}

function clearFilters() {
    filters.clinic = null;
    filters.period = null;
    filters.student = null;
    filters.date = '';
}

function confirm(item: any) {
    alert('Confirmado: ' + item.student);
}

function formatDate(date: string) {
    const [y, m, d] = date.split('-');
    return `${d}/${m}/${y}`;
}
</script>
