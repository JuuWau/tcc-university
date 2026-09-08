<script setup lang="ts">
import { computed, inject, reactive, watch } from 'vue';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { PatientScheduleBookingContextKey, PatientScheduleCreateModalKey } from '@/keys/patients/patientScheduleBookingKeys';
import { toast } from 'vue3-toastify';
import axios from 'axios';
import { patientScheduleBookingSchema } from '@/schemas/patientScheduleBooking.schema';
import FormFooter from '@/components/form/FormFooter.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormHeader from '@/components/form/FormHeader.vue';

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
			class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Novo agendamento"
				subtitle="Preencha os dados para criar o agendamento do paciente."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-5 py-5">
					<BaseInput
                        :model-value="initialData.patient"
                        label="Paciente"
                        type="text"
                        disabled
                    />

					<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
						<BaseInput
                            :model-value="initialData.date"
                            label="Data"
                            type="date"
                            disabled
                        />

						<BaseInput
							v-model="form.start_time"
							label="Início"
							type="text"
							v-mask="'##:##'"
							placeholder="HH:mm"
						/>

						<BaseInput
							v-model="form.end_time"
							label="Fim"
							type="text"
							v-mask="'##:##'"
							placeholder="HH:mm"
						/>
					</div>

					<AppMultiselect
						v-if="canSelectProcedure"
						v-model="form.procedure_id"
						:options="booking.procedureOptions.value"
						field-label="Procedimento"
						label="label"
						value-prop="value"
						track-by="value"
						:searchable="true"
						:can-clear="true"
						:close-on-select="true"
						:append-to-body="true"
						placeholder="Selecione o procedimento"
					/>

					<AppMultiselect
						v-model="form.status"
						:options="statusOptions"
						field-label="Status (*)"
						label="label"
						value-prop="value"
						:searchable="true"
						:close-on-select="true"
						:can-clear="false"
						:append-to-body="true"
						placeholder="Selecione o status"
					/>

					<div>
						<label
							class="mb-1 block text-sm font-medium text-gray-700"
						>
							Observações
						</label>

						<textarea
							v-model="form.notes"
							rows="4"
							class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition placeholder:text-gray-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
							placeholder="Digite alguma observação sobre o agendamento"
						/>
					</div>
				</div>
			</div>

			<FormFooter
				action="save"
				action-label="Agendar"
				@cancel="close"
				@save="createAppointment"
			/>
		</div>
	</div>
</template>