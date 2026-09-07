<script setup lang="ts">
import { computed, inject, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { PatientScheduleBookingContextKey, PatientScheduleViewModalKey, type PatientScheduleBookingAppointment,} from '@/keys/patients/patientScheduleBookingKeys';
import { formatDateBr } from '@/src/utils/formatters';
import { patientScheduleBookingSchema } from '@/schemas/patientScheduleBooking.schema';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { Pencil } from 'lucide-vue-next';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const modal = inject(PatientScheduleViewModalKey);

const booking = inject(PatientScheduleBookingContextKey);

if (!modal) {
    throw new Error(
        'PatientScheduleBookingAppointmentViewModal precisa estar dentro do provider.',
    );
}

if (!booking) {
    throw new Error(
        'PatientScheduleBookingAppointmentViewModal precisa estar dentro de PatientScheduleBooking.',
    );
}

const appointment = computed<PatientScheduleBookingAppointment | null>(
    () => modal.appointment.value,
);

const isEditing = ref(false);
const loading = ref(false);

const form = reactive({
    start_time: '',
    end_time: '',
    procedure_id: null as number | null,
    status: 'scheduled',
    notes: '',
});

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

const canEdit = computed(() => {
    if (!appointment.value) {
        return false;
    }

    return appointment.value.patient_id === booking.patientId;
});

const canSelectProcedure = computed(() => {
    return appointment.value?.allow_procedure_booking === true;
});

const statusLabel = computed(() => {
    const status = appointment.value?.status;

    const option = statusOptions.find(
        (item) => item.value === status,
    );

    return option?.label ?? status ?? '';
});

watch(
    () => modal.isOpen.value,
    async (isOpen) => {
        if (!isOpen || !appointment.value) {
            return;
        }

        isEditing.value = false;

        form.start_time = appointment.value.start_time;
        form.end_time = appointment.value.end_time;
        form.procedure_id = appointment.value.procedure_id;
        form.status = appointment.value.status;
        form.notes = appointment.value.notes ?? '';

        if (canEdit.value && canSelectProcedure.value) 
        {
            await booking.loadProcedures();
        }
    },
);

function startEditing() {
    if (!canEdit.value || !appointment.value) {
        return;
    }

    form.start_time = appointment.value.start_time;
    form.end_time = appointment.value.end_time;
    form.procedure_id = appointment.value.procedure_id;
    form.status = appointment.value.status;
    form.notes = appointment.value.notes ?? '';

    isEditing.value = true;
}

function cancelEditing() {
    if (!appointment.value) {
        return;
    }

    form.start_time = appointment.value.start_time;
    form.end_time = appointment.value.end_time;
    form.procedure_id = appointment.value.procedure_id;
    form.status = appointment.value.status;
    form.notes = appointment.value.notes ?? '';

    isEditing.value = false;
}

async function submit() {
    if (!appointment.value) {
        return;
    }

    const result = patientScheduleBookingSchema.safeParse(form);

    if (!result.success) {
        toast.error(
            result.error.issues[0].message,
        );

        return;
    }

    try {
        await axios.put(
            `/patient-calendar/${booking.patientId}/${appointment.value.id}`,
            {
                patient_id: booking.patientId,
                procedure_id: result.data.procedure_id,
                status: result.data.status,
                notes: result.data.notes,

                scheduled_start_at:
                    `${appointment.value.date} ${result.data.start_time}:00`,

                scheduled_end_at:
                    `${appointment.value.date} ${result.data.end_time}:00`,
            },
        );

        toast.success(
            'Agendamento atualizado com sucesso.',
        );

        isEditing.value = false;
        modal.isOpen.value = false;

        await booking.selectDate(
            appointment.value.date,
        );
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao atualizar agendamento.',
        );
    }
}

function close() {
    isEditing.value = false;
    modal.isOpen.value = false;
    modal.appointment.value = null;
}
</script>

<template>
	<div
		v-if="modal.isOpen.value && appointment"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				:title="isEditing ? 'Editar agendamento' : 'Agendamento'"
				:subtitle="
					!canEdit
						? 'Este agendamento pertence a outro paciente e não pode ser editado.'
						: isEditing
							? 'Atualize os dados do agendamento.'
							: 'Visualize os dados do agendamento.'
				"
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-5 py-5">
					<BaseInput
						:model-value="
							appointment.patient ??
							'Paciente não informado'
						"
						label="Paciente"
						type="text"
						disabled
					/>

					<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
						<BaseInput
							:model-value="formatDateBr(appointment.date)"
							label="Data"
							type="text"
							disabled
						/>

						<div>
							<BaseInput
								v-if="isEditing"
								v-model="form.start_time"
								label="Início"
								type="text"
								v-mask="'##:##'"
								placeholder="HH:mm"
							/>

							<BaseInput
								v-else
								:model-value="appointment.start_time"
								label="Início"
								type="text"
								disabled
							/>
						</div>

						<div>
							<BaseInput
								v-if="isEditing"
								v-model="form.end_time"
								label="Fim"
								type="text"
								v-mask="'##:##'"
								placeholder="HH:mm"
							/>

							<BaseInput
								v-else
								:model-value="appointment.end_time"
								label="Fim"
								type="text"
								disabled
							/>
						</div>
					</div>

					<div>
						<AppMultiselect
							v-if="isEditing && canSelectProcedure"
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

						<BaseInput
							v-else
							:model-value="
								appointment.procedure ??
								'Não informado'
							"
							label="Procedimento"
							type="text"
							disabled
						/>
					</div>

					<div>
						<AppMultiselect
							v-if="isEditing"
							v-model="form.status"
							:options="statusOptions"
							field-label="Status"
							label="label"
							value-prop="value"
							:searchable="true"
							:close-on-select="true"
							:can-clear="false"
							:append-to-body="true"
							placeholder="Selecione o status"
						/>

						<BaseInput
							v-else
							:model-value="statusLabel"
							label="Status"
							type="text"
							disabled
						/>
					</div>

					<div>
						<label
							class="mb-1 block text-sm font-medium text-gray-700"
						>
							Observações
						</label>

						<textarea
							v-if="isEditing"
							v-model="form.notes"
							rows="4"
							class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition placeholder:text-gray-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
							placeholder="Digite alguma observação sobre o agendamento"
						/>

						<div
							v-else
							class="min-h-[100px] w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm whitespace-pre-wrap text-gray-700"
						>
							{{
								appointment.notes ||
								'Nenhuma observação.'
							}}
						</div>
					</div>
				</div>
			</div>

			<div
				class="flex shrink-0 justify-end gap-2 border-t border-gray-200 bg-white p-4"
			>
				<template v-if="!isEditing">
					<CancelButton
						label="Fechar"
						@click="close"
					/>

					<Button
						v-if="canEdit"
						type="button"
						class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 active:scale-[0.98]"
						@click="startEditing"
					>
						<Pencil class="h-4 w-4" />
						Editar
					</Button>
				</template>

				<template v-else>
					<CancelButton
						label="Cancelar"
						@click="cancelEditing"
					/>

					<SaveButton
						:loading="loading"
						@click="submit"
					>
						Salvar alterações
					</SaveButton>
				</template>
			</div>
		</div>
	</div>
</template>