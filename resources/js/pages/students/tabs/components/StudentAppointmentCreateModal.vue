<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { AppointmentCreateModalKey } from '@/keys/appointment/useAppointmentKeys';
import {
    StudentScheduleContextKey,
    type StudentScheduleContext,
} from '@/keys/students/studentScheduleKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { appointmentCreateSchema } from '@/schemas/appointmentCreateSchema';
import { getTodayDateKey } from '@/src/utils/formatters';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const loading = inject(LoadingKey);

const modal = inject(AppointmentCreateModalKey);

const schedule = inject(StudentScheduleContextKey) as StudentScheduleContext;

if (!modal) {
    throw new Error(
        'StudentAppointmentCreateModal precisa estar dentro do provider',
    );
}

const initialData = computed(() => modal.initialData.value);

const todayDateKey = getTodayDateKey();

function close() {
    clearErrors();
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

const errors = reactive({
    patient_id: '',
    date: '',
    start_time: '',
    end_time: '',
    procedure_id: '',
    status: '',
});

const patientInput = ref<{ focus: () => void } | null>(null);
const dateInput = ref<{ focus: () => void } | null>(null);
const startTimeInput = ref<{ focus: () => void } | null>(null);
const endTimeInput = ref<{ focus: () => void } | null>(null);
const procedureInput = ref<{ focus: () => void } | null>(null);
const statusInput = ref<{ focus: () => void } | null>(null);

function clearErrors() {
    errors.patient_id = '';
    errors.date = '';
    errors.start_time = '';
    errors.end_time = '';
    errors.procedure_id = '';
    errors.status = '';
}

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

        clearErrors();

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

    clearErrors();

    const result = appointmentCreateSchema.safeParse(form);

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0] as keyof typeof errors;

            if (field in errors) {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0]?.path[0];

        if (firstError === 'patient_id') {
            patientInput.value?.focus();
        } else if (firstError === 'date') {
            dateInput.value?.focus();
        } else if (firstError === 'start_time') {
            startTimeInput.value?.focus();
        } else if (firstError === 'end_time') {
            endTimeInput.value?.focus();
        } else if (firstError === 'procedure_id') {
            procedureInput.value?.focus();
        } else if (firstError === 'status') {
            statusInput.value?.focus();
        }

        return;
    }

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

        await schedule.selectDate(result.data.date);

        toast.success('Agendamento criado com sucesso');

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao criar agendamento',
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
        <div
            class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Novo agendamento"
                subtitle="Preencha os dados para criar um novo agendamento."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <div class="space-y-5 py-5">
                    <AppMultiselect
                        ref="patientInput"
                        v-model="form.patient_id"
                        :options="modal.patientOptions.value"
                        field-label="Paciente"
                        label="label"
                        track-by="value"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        :append-to-body="true"
                        placeholder="Selecione o paciente"
                        required
                        :error="errors.patient_id"
                    />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <BaseInput
                            ref="dateInput"
                            v-model="form.date"
                            label="Data"
                            type="date"
                            :min="todayDateKey"
                            required
                            :error="errors.date"
                        />

                        <BaseInput
                            ref="startTimeInput"
                            v-model="form.start_time"
                            label="Início"
                            type="text"
                            v-mask="'##:##'"
                            placeholder="HH:mm"
                            required
                            :error="errors.start_time"
                        />

                        <BaseInput
                            ref="endTimeInput"
                            v-model="form.end_time"
                            label="Fim"
                            type="text"
                            v-mask="'##:##'"
                            placeholder="HH:mm"
                            required
                            :error="errors.end_time"
                        />
                    </div>

                    <AppMultiselect
                        v-if="schedule.allowProcedureBooking.value"
                        ref="procedureInput"
                        v-model="form.procedure_id"
                        :options="modal.procedureOptions.value"
                        field-label="Procedimento"
                        label="label"
                        track-by="value"
                        value-prop="value"
                        :searchable="true"
                        :can-clear="true"
                        :close-on-select="true"
                        :append-to-body="true"
                        placeholder="Selecione o procedimento"
                        :error="errors.procedure_id"
                    />

                    <AppMultiselect
                        ref="statusInput"
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
                        required
                        :error="errors.status"
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
                @save="save"
            />
        </div>
    </div>
</template>
