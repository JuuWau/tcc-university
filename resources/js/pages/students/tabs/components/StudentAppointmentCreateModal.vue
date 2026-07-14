```vue
<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { AppointmentCreateModalKey } from '@/keys/appointment/useAppointmentKeys';
import {
    StudentScheduleContextKey,
    type StudentScheduleContext,
} from '@/keys/students/studentScheduleKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { appointmentCreateSchema } from '@/schemas/appointmentCreateSchema';
import { getTodayDateKey } from '@/src/utils/formatters';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

const loading = inject(LoadingKey);

const modal = inject(
    AppointmentCreateModalKey,
);

const schedule = inject(
    StudentScheduleContextKey,
) as StudentScheduleContext;

if (!modal) {
    throw new Error(
        'StudentAppointmentCreateModal precisa estar dentro do provider',
    );
}

const initialData = computed(
    () => modal.initialData.value,
);

const todayDateKey = getTodayDateKey();

function close() {
    modal.isOpen.value = false;
}

const form = reactive({
    status: 'scheduled',
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
    initialData,
    (value) => {
        if (!value) {
            return;
        }

        form.patient_id = null;
        form.procedure_id = null;

        form.status = 'scheduled';

        form.date = value.date;
        form.start_time = value.start_time;
        form.end_time = value.end_time;

        form.notes = '';
    },
    {
        immediate: true,
    },
);

async function save() {
    if (loading?.value) {
        return;
    }

    const result = appointmentCreateSchema.safeParse(form);

    if (!result.success) {
        toast.error(
            result.error.issues[0].message,
        );

        return;
    }
    console.log('aa', schedule.scheduleEnrollmentId.value,)

    try {
        loading!.value = true;

        const response = await axios.post(
            `/student-calendar/${schedule.studentId}`,
            {
                schedule_enrollment_id: schedule.scheduleEnrollmentId.value,
                patient_id: result.data.patient_id,
                procedure_id: result.data.procedure_id,
                status: result.data.status,
                scheduled_start_at: `${result.data.date} ${result.data.start_time}:00`,
                scheduled_end_at: `${result.data.date} ${result.data.end_time}:00`,
                notes: result.data.notes,
            },
        );

        await schedule.selectDate(
            result.data.date,
        );

        toast.success(
            'Agendamento criado com sucesso',
        );

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message
            ?? 'Erro ao criar agendamento',
        );
    } finally {
        loading!.value = false;
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-2xl rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold">
                    Novo Agendamento
                </h2>
            </div>

            <hr />

            <div class="space-y-4 pt-4">
                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
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
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
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
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
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
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
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

                <div
                    v-if="schedule.allowProcedureBooking.value"
                >
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
                <CancelButton @click="close" />

                <SaveButton
                    :loading="loading"
                    @click.stop="save"
                />
            </div>
        </div>
    </div>
</template>
```
