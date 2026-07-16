<script setup lang="ts">
import { AttendanceKey } from '@/keys/attendance/attendanceKeys';
import { computed, inject, watch } from 'vue';
import { Button } from '@/components/ui/button';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { useAttendance } from '@/composables/attendance/useAttendance';
import { Switch } from '@headlessui/vue';
import { toast } from 'vue3-toastify';

const attendance = inject(AttendanceKey);

const attendanceApi = useAttendance();

if (!attendance) {
    throw new Error('AttendanceKey não encontrado.');
}

const selectedDateInfo = computed(() =>
    attendance.dates.value.find(
        date => date.id === attendance.selectedDate.value
    )
);

const canEditAttendance = computed(() =>
    selectedDateInfo.value?.editable ?? false
);

const hasSelectedDate = computed(() =>
    !!attendance.selectedDate.value
);

const periodOptions = computed(() =>
    attendance.periods.value.map(period => ({
        value: period.id,
        label: period.label,
    })),
);

const dateOptions = computed(() =>
    attendance.dates.value.map(date => ({
        value: date.id,
        label: date.label,
    })),
);

watch(
    () => attendance.selectedPeriodId.value,
    async (periodId) => {
        attendance.selectedDate.value = null;
        attendance.students.value = [];

        if (!periodId) {
            attendance.dates.value = [];
            return;
        }

        const dates = await attendanceApi.loadDates(
            attendance.clinic.value.clinic_id,
            periodId,
        );

        attendance.dates.value = dates;
    },
);

watch(
    () => attendance.selectedDate.value,
    async (slotId) => {
        attendance.students.value = [];

        if (!slotId) {
            return;
        }

        attendance.students.value = await attendanceApi.loadStudents(slotId);
    },
);

function clearFilters() {
    attendance.selectedPeriodId.value = null;
    attendance.selectedDate.value = null;
    attendance.students.value = [];
}

async function saveAttendance() {
    if (!canEditAttendance.value) {
        toast.warning('Essa data não permite alteração de presença.');
        return;
    }

    if (!attendance.selectedDate.value) {
        return;
    }

    try {
        const response = await attendanceApi.updateAttendance(
            attendance.selectedDate.value,
            attendance.students.value.map(student => ({
                id: student.id,
                attended: student.attended,
            }))
        );

        toast.success(response.message);
    } catch (error) {
        toast.error('Erro ao salvar presença.');
    }
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">
                Controle de Comparecimento
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                {{ attendance.clinic.value.clinic_name }}
            </p>
        </div>

        <div class="mb-6 grid items-end gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label
                    for="period_id"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Período
                </label>
                <AppMultiselect
                        id="period_id"
                        v-model="attendance.selectedPeriodId.value"
                        :options="periodOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Todos os períodos"
                />
            </div>

            <div>
                <label
                    for="date"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Data
                </label>
                <AppMultiselect
                    v-model="attendance.selectedDate.value"
                    :options="dateOptions"
                    label="label"
                    value-prop="value"
                    :searchable="true"
                    :close-on-select="true"
                    :can-clear="true"
                    :append-to-body="true"
                    placeholder="Todas as datas"
                />
            </div>

            <div class="flex h-full flex-col justify-end gap-2">
                <Button
                    variant="outline"
                    class="flex w-full items-center justify-center gap-2 cursor-pointer"
                    @click="clearFilters"
                >
                    <X class="h-4 w-4" />
                    Limpar
                </Button>
            </div>
        </div>

        <div
            v-if="hasSelectedDate && !canEditAttendance"
            class="mb-4 rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-700"
            >
            A presença desta data não pode ser alterada. 
            Apenas datas do dia atual podem receber alterações.
        </div>
        
        <div
            v-if="!attendance.students.value.length"
            class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
        >
            Selecione um período e uma data para visualizar os alunos.
        </div>
    
        <div
            v-else
            class="overflow-hidden rounded-lg border border-gray-200"
        >
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            RA
                        </th>

                        <th
                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            Aluno
                        </th>

                        <th
                            class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                        >
                            Compareceu
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                        v-for="student in attendance.students.value"
                        :key="student.enrollment_id"
                    >
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-500">
                                {{ student.registration }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ student.name }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <Switch
                                v-model="student.attended"
                                :disabled="!canEditAttendance"
                                :class="[
                                    student.attended ? 'bg-sky-600' : 'bg-gray-300',
                                    !canEditAttendance ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                                    'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition'
                                ]"
                            >
                                <span
                                    :class="[
                                        student.attended ? 'translate-x-6' : 'translate-x-1',
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition'
                                    ]"
                                />
                            </Switch>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                class="flex justify-end border-t border-gray-200 bg-gray-50 p-4"
            >
                <Button
                    :disabled="!canEditAttendance"
                    class="bg-sky-600 hover:bg-sky-700 cursor-pointer text-white font-semibold py-2 px-4 rounded disabled:opacity-50"
                    @click="saveAttendance"
                >
                    Salvar presença
                </Button>
            </div>
        </div>
    </div>
</template>