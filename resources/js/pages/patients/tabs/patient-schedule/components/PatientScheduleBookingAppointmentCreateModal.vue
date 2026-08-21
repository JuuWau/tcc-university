<script setup lang="ts">
import { computed, inject, reactive, watch } from 'vue';
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { PatientScheduleBookingContextKey, PatientScheduleCreateModalKey } from '@/keys/patients/patientScheduleBookingKeys';
import { toast } from 'vue3-toastify';
import axios from 'axios';
import { patientScheduleBookingSchema } from '@/schemas/patientScheduleBooking.schema';

const modal = inject(PatientScheduleCreateModalKey);

const booking = inject(PatientScheduleBookingContextKey);

if (!modal) {
    throw new Error(
        'PatientScheduleBookingAppointmentCreateModal precisa estar dentro do provider.',
    );
}

if (!booking) {
    throw new Error(
        'PatientScheduleBookingAppointmentCreateModal precisa estar dentro de PatientScheduleBooking.',
    );
}

const form = reactive({
    procedure_id: null as number | null,
    start_time: '',
    end_time: '',
    status: 'scheduled',
    notes: '',
});

const initialData = computed(
    () => modal.initialData.value,
);

const canSelectProcedure = computed(
    () => initialData.value.allow_procedure_booking,
);

const statusOptions = [
    {
        label: 'Agendado',
        value: 'scheduled',
    },
    {
        label: 'Confirmado',
        value: 'confirmed',
    },
    {
        label: 'Concluído',
        value: 'completed',
    },
    {
        label: 'Cancelado',
        value: 'canceled',
    },
    {
        label: 'Não compareceu',
        value: 'no_show',
    },
    {
        label: 'Remarcado',
        value: 'rescheduled',
    },
];

watch(
    () => modal.isOpen.value,
    async (isOpen) => {
        if (!isOpen) {
            return;
        }

        form.procedure_id = null;
        form.start_time = initialData.value.start_time;
        form.end_time = initialData.value.end_time;
        form.status = initialData.value.status;
        form.notes = initialData.value.notes;

        if (initialData.value.allow_procedure_booking) {
            await booking.loadProcedures();
        }
    },
);

function close() {
    modal.isOpen.value = false;
}

async function createAppointment() {
    const result = patientScheduleBookingSchema.safeParse(form);

    if (!result.success) {
        toast.error(
            result.error.issues[0].message,
        );

        return;
    }

    try {
        const response = await axios.post(
            `/patient-calendar/${initialData.value.patient_id}`,
            {
                schedule_enrollment_id: initialData.value.schedule_enrollment_id,
                patient_id: initialData.value.patient_id,
                procedure_id: result.data.procedure_id,
                status: result.data.status,
                notes: result.data.notes,
                scheduled_start_at: `${initialData.value.date} ${result.data.start_time}:00`,
                scheduled_end_at: `${initialData.value.date} ${result.data.end_time}:00`,
            },
        );

        const appointment = response.data.data;

        await booking.selectDate(appointment.date);

        toast.success('Agendamento criado com sucesso.',);
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao criar agendamento.',
        );
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="w-full max-w-2xl rounded-lg bg-white p-6"
        >
            <div
                class="mb-4 flex items-center justify-between"
            >
                <h2 class="text-lg font-bold">
                    Novo agendamento
                </h2>

                <button
                    type="button"
                    class="text-xl text-gray-400 hover:text-gray-600"
                    @click="close"
                >
                    ×
                </button>
            </div>

            <hr />

            <div class="space-y-4 pt-4">
                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Paciente
                    </label>

                    <input
                        :value="initialData.patient"
                        type="text"
                        readonly
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    />
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700">
                            Data
                        </label>

                        <input
                            :value="initialData.date"
                            type="date"
                            readonly
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700">
                            Início
                        </label>

                        <input
                            v-model="form.start_time"
                            type="text"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700">
                            Fim
                        </label>

                        <input
                            v-model="form.end_time"
                            type="text"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div v-if="canSelectProcedure">
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Procedimento
                    </label>

                    <AppMultiselect
                        v-model="form.procedure_id"
                        :options="booking.procedureOptions.value"
                        label="label"
                        track-by="value"
                        value-prop="value"
                        :searchable="true"
                        :can-clear="true"
                        :close-on-select="true"
                        placeholder="Selecione o procedimento"
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Status (*)
                    </label>

                    <AppMultiselect
                        v-model="form.status"
                        :options="statusOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        :append-to-body="true"
                        placeholder="Selecione o status"
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Observações
                    </label>

                    <textarea
                        v-model="form.notes"
                        rows="4"
                        class="w-full rounded border border-gray-200 px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <CancelButton
                    @click="close"
                />

                <CreateButton
                    @click="createAppointment"
                >
                    Agendar
                </CreateButton>
            </div>
        </div>
    </div>
</template>