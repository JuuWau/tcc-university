<script setup lang="ts">
import { computed, inject, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { PatientScheduleBookingContextKey, PatientScheduleViewModalKey, type PatientScheduleBookingAppointment,} from '@/keys/patients/patientScheduleBookingKeys';
import { formatDateBr } from '@/src/utils/formatters';
import { patientScheduleBookingSchema } from '@/schemas/patientScheduleBooking.schema';

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
            class="w-full max-w-2xl rounded-lg bg-white p-6"
        >
            <div
                class="mb-4 flex items-center justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-bold text-gray-900"
                    >
                        {{ isEditing
                            ? 'Editar agendamento'
                            : 'Agendamento' }}
                    </h2>

                    <p
                        v-if="!canEdit"
                        class="mt-1 text-xs text-gray-500"
                    >
                        Este agendamento pertence a outro
                        paciente e não pode ser editado.
                    </p>
                </div>

                <button
                    type="button"
                    class="text-2xl text-gray-400 hover:text-gray-600"
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

                    <div
                        class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                    >
                        {{
                            appointment.patient ??
                            'Paciente não informado'
                        }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Data
                        </label>

                        <div
                            class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                        >
                            {{ formatDateBr(appointment.date) }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Início
                        </label>

                        <input
                            v-if="isEditing"
                            v-model="form.start_time"
                            type="text"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />

                        <div
                            v-else
                            class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                        >
                            {{ appointment.start_time }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Fim
                        </label>

                        <input
                            v-if="isEditing"
                            v-model="form.end_time"
                            type="text"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />

                        <div
                            v-else
                            class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                        >
                            {{ appointment.end_time }}
                        </div>
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Procedimento
                    </label>

                    <AppMultiselect
                        v-if="
                            isEditing &&
                            canSelectProcedure
                        "
                        v-model="form.procedure_id"
                        :options="
                            booking.procedureOptions.value
                        "
                        label="label"
                        track-by="value"
                        value-prop="value"
                        :searchable="true"
                        :can-clear="true"
                        :close-on-select="true"
                        placeholder="Selecione o procedimento"
                    />

                    <div
                        v-else
                        class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                    >
                        {{
                            appointment.procedure ??
                            'Não informado'
                        }}
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Status
                    </label>

                    <AppMultiselect
                        v-if="isEditing"
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

                    <div
                        v-else
                        class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                    >
                        {{ statusLabel }}
                    </div>
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
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    />

                    <div
                        v-else
                        class="min-h-[100px] w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm whitespace-pre-wrap text-gray-700"
                    >
                        {{
                            appointment.notes ||
                            'Nenhuma observação.'
                        }}
                    </div>
                </div>
            </div>

            <div
                class="flex justify-end gap-2 pt-4"
            >
                <template v-if="!isEditing">
                    <button
                        type="button"
                        class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                        @click="close"
                    >
                        Fechar
                    </button>

                    <button
                        v-if="canEdit"
                        type="button"
                        class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700"
                        @click="startEditing"
                    >
                        Editar
                    </button>
                </template>

                <template v-else>
                    <button
                        type="button"
                        class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 disabled:opacity-50"
                        :disabled="loading"
                        @click="cancelEditing"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="loading"
                        @click="submit"
                    >
                        {{
                            loading
                                ? 'Salvando...'
                                : 'Salvar alterações'
                        }}
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>