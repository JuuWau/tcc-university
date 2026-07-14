<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { AppointmentDetailsModalKey } from '@/keys/appointment/useAppointmentKeys';
import { StudentScheduleContextKey, type StudentScheduleContext } from '@/keys/students/studentScheduleKeys';
import { computed, inject, reactive, watch } from 'vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { toast } from 'vue3-toastify';
import { appointmentUpdateSchema } from '@/schemas/appointmentUpdateSchema';
import axios from 'axios';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { getTodayDateKey } from '@/src/utils/formatters';

const loading = inject(LoadingKey);

const modal = inject(
    AppointmentDetailsModalKey,
);

const schedule = inject(
    StudentScheduleContextKey,
) as StudentScheduleContext;

const todayDateKey = getTodayDateKey();

if (!modal) {
    throw new Error(
        'StudentAppointmentEditModal precisa estar dentro do provider',
    );
}

const appointment = computed(
    () => modal.appointment.value,
);

const canSelectProcedure = computed(
    () => appointment.value?.allow_procedure_booking,
);

function close() {
    modal.isOpen.value = false;
}

const form = reactive({
    status: '',
    date: '',
    start_time: '',
    end_time: '',
    notes: '',
    patient_id: null as number | null,
    procedure_id: null as number | null,
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

watch(
    appointment,
    (value) => {
        if (!value) {
            return;
        }

        form.patient_id = value.patient_id;
        form.status = value.status;
        form.date = value.date;
        form.start_time = value.start_time;
        form.end_time = value.end_time;
        form.notes = value.notes ?? '';
        form.procedure_id = value.procedure_id;
    },
    { immediate: true },
);

async function save() {
    if (!appointment.value?.id || loading?.value) {
        return;
    }

    const result = appointmentUpdateSchema.safeParse(form);

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading!.value = true;

        const res = await axios.put(
            `/student-calendar/${appointment.value.id}`,
            {
                patient_id: result.data.patient_id,
                procedure_id: result.data.procedure_id,
                status: result.data.status,
                scheduled_start_at: `${result.data.date} ${result.data.start_time}:00`,
                scheduled_end_at: `${result.data.date} ${result.data.end_time}:00`,
                notes: result.data.notes,
            },
        );
        const updatedAppointment = res.data.data;

        await schedule.selectDay({
            date: updatedAppointment.date,
        });

        toast.success('Agendamento atualizado com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao atualizar agendamento',
        );
    } finally {
        loading!.value = false;
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value && appointment"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-2xl rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold">
                    Agendamento
                </h2>
            </div>

            <hr />

            <div class="space-y-4 pt-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Paciente (*)
                    </label>

                    <AppMultiselect
                        v-model="form.patient_id"
                        :options="modal.patientOptions.value"
                        label="label"
                        track-by="value"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        placeholder="Selecione o paciente"
                   />
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Data (*)
                        </label>
                        <input
                                v-model="form.date"
                                type="date"
                                :min="todayDateKey"
                                class="w-full rounded border border-gray-200 px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Início (*)
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
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Fim (*)
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

                <div>
                    <div v-if="canSelectProcedure">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Procedimento
                        </label>

                        <AppMultiselect
                            v-model="form.procedure_id"
                            :options="modal.procedureOptions.value"
                            label="label"
                            track-by="value"
                            value-prop="value"
                            :searchable="true"
                            :can-clear="true"
                            :close-on-select="true"
                            placeholder="Selecione o procedimento"
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
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
                    <label class="mb-1 block text-sm font-medium text-gray-700">
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
                <CancelButton @click="close" />

                <SaveButton
                    class="rounded-md bg-sky-600 px-4 py-2 text-white cursor-pointer"
                    @click="save"
                >
                    Salvar
                </SaveButton>
            </div>
        </div>
    </div>
</template>
