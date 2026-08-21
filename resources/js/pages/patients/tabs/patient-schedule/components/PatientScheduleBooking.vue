```vue
<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import { PatientScheduleBookingContextKey, } from '@/keys/patients/patientScheduleBookingKeys';
import { inject, onMounted } from 'vue';
import PatientScheduleBookingCalendar from './PatientScheduleBookingCalendar.vue';

const booking = inject(PatientScheduleBookingContextKey);

if (!booking) {
    throw new Error(
        'PatientScheduleBookingContext não foi fornecido.',
    );
}

onMounted(async () => {
    await booking.loadClinics();
});

const clinics = booking.clinics;
const periods = booking.periods;
const students = booking.students;

const clinicId = booking.clinicId;
const periodId = booking.periodId;
const studentId = booking.studentId;

const canShowCalendar = booking.canShowCalendar;
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Clínica
                </label>

                <AppMultiselect
                    v-model="clinicId"
                    :options="clinics"
                    label="label"
                    value-prop="value"
                    placeholder="Selecione uma clínica"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                />
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Período
                </label>

                <AppMultiselect
                    v-model="periodId"
                    :options="periods"
                    label="label"
                    value-prop="value"
                    placeholder="Selecione um período"
                    :searchable="true"
                    :can-clear="true"
                    :close-on-select="true"
                    :disabled="!clinicId"
                />
            </div>

            <div>
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700"
                >
                    Estudante
                </label>

                <AppMultiselect
                    v-model="studentId"
                    :options="students"
                    label="label"
                    value-prop="value"
                    placeholder="Selecione um estudante"
                    :searchable="true"
                    :can-clear="true"
                    :close-on-select="true"
                    :disabled="!periodId"
                />
            </div>
        </div>

        <div
            v-if="canShowCalendar"
            class="border-t border-gray-200 pt-6"
        >
            <div class="mb-5">
                <h3 class="font-semibold text-gray-900">
                    Escolha o dia e horário
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Selecione um dia disponível no calendário para visualizar
                    os horários de atendimento.
                </p>
            </div>

            <PatientScheduleBookingCalendar />
        </div>

        <div
            v-else
            class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center"
        >
            <p class="text-sm font-medium text-gray-600">
                Selecione a clínica, o período e o estudante.
            </p>

            <p class="mt-1 text-sm text-gray-400">
                Depois disso, você poderá escolher a data e o horário do
                agendamento.
            </p>
        </div>
    </div>
</template>
