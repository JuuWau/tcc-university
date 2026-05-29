<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import Multiselect from '@vueform/multiselect';
import { CalendarDays, Search, Save, X } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

const filters = reactive({
    clinic_id: null as number | null,
    period_id: null as number | null,
    date: '',
});

const clinics = [
    {
        value: 1,
        label: 'Clínica Escola de Psicologia',
    },
    {
        value: 2,
        label: 'Clínica de Fisioterapia',
    },
];

const periods = [
    {
        value: 1,
        label: '2025.1 - Noturno',
    },
    {
        value: 2,
        label: '2025.1 - Integral',
    },
];

const students = ref([
    {
        id: 1,
        time: '08:00 - 08:50',
        name: 'Ana Maria Silva',
        registration: '202310001',
        responsible: 'Prof. João Ferreira',
        attendance: 'present',
    },
    {
        id: 2,
        time: '09:00 - 09:50',
        name: 'Bruno Rodrigues',
        registration: '202310002',
        responsible: 'Prof. João Ferreira',
        attendance: 'absent',
    },
    {
        id: 3,
        time: '10:00 - 10:50',
        name: 'Carla Lima Santos',
        registration: '202310003',
        responsible: 'Prof. Ana Paula',
        attendance: 'present',
    },
]);

function clearFilters() {
    filters.clinic_id = null;
    filters.period_id = null;
    filters.date = '';
}

function search() {
    console.log(filters);
}

function saveAttendance() {
    console.log(students.value);
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Chamada de Alunos
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Selecione os filtros para visualizar os alunos agendados e
                    registrar presença.
                </p>
            </div>

            <div
                class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
            >
                <h2 class="mb-4 text-lg font-semibold text-gray-800">
                    Filtros
                </h2>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Clínica
                        </label>

                        <Multiselect
                            v-model="filters.clinic_id"
                            :options="clinics"
                            placeholder="Selecione a clínica"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Período
                        </label>

                        <Multiselect
                            v-model="filters.period_id"
                            :options="periods"
                            placeholder="Selecione o período"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Data
                        </label>

                        <div class="relative">
                            <input
                                v-model="filters.date"
                                type="date"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            />

                            <CalendarDays
                                class="absolute top-2.5 right-3 h-4 w-4 text-gray-400"
                            />
                        </div>
                    </div>

                    <div class="flex items-end gap-2">
                        <Button
                            variant="outline"
                            class="flex-1"
                            @click="clearFilters"
                        >
                            <X class="h-4 w-4" />
                            Limpar
                        </Button>

                        <Button
                            class="flex-1"
                            @click="search"
                        >
                            <Search class="h-4 w-4" />
                            Buscar
                        </Button>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-200 p-5"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Alunos Agendados
                        </h2>

                        <p class="text-sm text-gray-500">
                            Total: {{ students.length }} alunos
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                                >
                                    Horário
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                                >
                                    Aluno
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                                >
                                    Matrícula
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                                >
                                    Responsável
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                                >
                                    Presença
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="student in students"
                                :key="student.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    {{ student.time }}
                                </td>

                                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                    {{ student.name }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700">
                                    {{ student.registration }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700">
                                    {{ student.responsible }}
                                </td>

                                <td class="px-4 py-4">
                                    <select
                                        v-model="student.attendance"
                                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                                    >
                                        <option value="present">
                                            Presente
                                        </option>

                                        <option value="absent">
                                            Não compareceu
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="flex items-center justify-end border-t border-gray-200 p-5"
                >
                    <Button @click="saveAttendance">
                        <Save class="h-4 w-4" />
                        Salvar chamada
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>