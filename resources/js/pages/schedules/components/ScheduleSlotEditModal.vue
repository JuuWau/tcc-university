<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ScheduleSlotEditKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { scheduleSlotUpdateSchema } from '@/schemas/scheduleSlotUpdate.schema';
import type { AppPageProps } from '@/types/index';
import type {
    OpenClinicScheduleClinic,
    OpenClinicScheduleResponsibleOption,
    OpenClinicScheduleRow,
    OpenClinicSchedulesFilters,
} from '@/types/schedule/openClinicSchedules';
import { Switch } from '@headlessui/vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

type Page = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;

const editModal = inject(ScheduleSlotEditKey);
const loading = inject(LoadingKey);

if (!editModal) {
    throw new Error('ScheduleSlotEditModal precisa estar dentro do provider');
}

const page = usePage<Page>();

const responsibleOptions = computed(() =>
    page.props.responsible.map((r) => ({ label: r.label, value: r.id })),
);

const form = reactive({
    responsible_id: null as number | null,
    date: '',
    start_time: '',
    end_time: '',
    available_slots: '' as string | number,
    allow_student_booking: false,
    allow_student_enrollment: false,
    allow_procedure_booking: false,
});

function timeToInput(value: string): string {
    return String(value).slice(0, 5);
}

watch(
    () => editModal.row.value,
    (row: OpenClinicScheduleRow | null) => {
        if (!row) return;
        form.responsible_id = row.responsible_id;
        form.date = String(row.date).slice(0, 10);
        form.start_time = timeToInput(row.start_time);
        form.end_time = timeToInput(row.end_time);
        form.available_slots = row.available_slots;
        form.allow_student_booking = row.allow_student_booking;
        form.allow_student_enrollment = row.allow_student_enrollment;
        form.allow_procedure_booking = row.allow_procedure_booking;
    },
    { immediate: true },
);

function close() {
    editModal.isOpen.value = false;
}

async function submit() {
    const row = editModal.row.value;
    if (!row || loading?.value) return;
    const result = scheduleSlotUpdateSchema.safeParse({
        period_id: row.period_id,
        responsible_id: form.responsible_id,
        date: form.date,
        start_time: form.start_time,
        end_time: form.end_time,
        available_slots: form.available_slots,
        allow_student_booking: form.allow_student_booking,
        allow_student_enrollment: form.allow_student_enrollment,
        allow_procedure_booking: form.allow_procedure_booking,
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;
        await axios.patch(`/schedules/slots/${row.id}`, {
            period_id: result.data.period_id,
            responsible_id: result.data.responsible_id,
            date: result.data.date,
            start_time: result.data.start_time,
            end_time: result.data.end_time,
            available_slots: result.data.available_slots,
            allow_student_booking: result.data.allow_student_booking,
            allow_student_enrollment: result.data.allow_student_enrollment,
            allow_procedure_booking: result.data.allow_procedure_booking,
        });
        toast.success('Agenda atualizada com sucesso');
        close();
        router.reload({ preserveUrl: true });
    } catch (error: any) {
        const err = error.response?.data;
        if (err?.conflict) {
            toast.error(
                `${err.message ?? 'Conflito de agenda.'} (${err.conflict.date} ${err.conflict.start_time}–${err.conflict.end_time})`,
            );
            return;
        }
        toast.error(err?.message ?? 'Erro ao atualizar agenda');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="editModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div
            class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">Editar agenda</h2>
            <hr />

            <div class="space-y-4 py-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Responsável
                    </label>
                    <AppMultiselect
                        v-model="form.responsible_id"
                        :options="responsibleOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        placeholder="Responsável"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Data (*)
                    </label>
                    <input
                        v-model="form.date"
                        type="date"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
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
                            class="mb-2 block text-sm font-medium text-gray-700"
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

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Vagas disponíveis
                    </label>
                    <input
                        v-model.number="form.available_slots"
                        type="number"
                        min="0"
                        step="1"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    />
                </div>

                <div class="md:col-span-2 flex items-center justify-between gap-4 rounded-md border border-gray-200 px-3 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">
                            Permitir inscrição de alunos
                        </p>
                        <p class="text-xs text-gray-500">
                            Se desativado, os alunos não poderão se inscrever nesses horários, deverá ser gerenciada manualmente a ocupação das vagas pela equipe da clínica.
                        </p>
                    </div>

                    <Switch
                        v-model="form.allow_student_booking"
                        :class="[
                            form.allow_student_booking ? 'bg-sky-600' : 'bg-gray-300',
                            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition cursor-pointer'
                        ]"
                    >
                        <span
                            :class="[
                                form.allow_student_booking ? 'translate-x-6' : 'translate-x-1',
                                'inline-block h-4 w-4 transform rounded-full bg-white transition'
                            ]"
                        />
                    </Switch>
                </div>

                <div class="md:col-span-2 flex items-center justify-between gap-4 rounded-md border border-gray-200 px-3 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">
                            Ativar incrição de alunos do período automaticamente para os horarios selecionados
                        </p>
                        <p class="text-xs text-gray-500">
                            Se ativo, os alunos do período selecionado serão inscritos automaticamente em todos os horários que abrirem para a clínica nessa página, sem necessidade de inscrição manual, se for necessário edição ou cancelamento de inscrição desses alunos, isso deverá ser feito manualmente pela equipe da clínica.
                        </p>
                    </div>

                    <Switch
                        v-model="form.allow_student_enrollment"
                        :class="[
                            form.allow_student_enrollment ? 'bg-sky-600' : 'bg-gray-300',
                            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition cursor-pointer'
                        ]"
                    >
                        <span
                            :class="[
                                form.allow_student_enrollment ? 'translate-x-6' : 'translate-x-1',
                                'inline-block h-4 w-4 transform rounded-full bg-white transition'
                            ]"
                        />
                    </Switch>
                </div>

                <div class="md:col-span-2 flex items-center justify-between gap-4 rounded-md border border-gray-200 px-3 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">
                            Permitir registro de procedimento
                        </p>
                        <p class="text-xs text-gray-500">
                            Se desativado, os alunos não poderão cadastrar procedimentos no agendamento do paciente.
                        </p>
                    </div>

                    <Switch
                        v-model="form.allow_procedure_booking"
                        :class="[
                            form.allow_procedure_booking ? 'bg-sky-600' : 'bg-gray-300',
                            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition cursor-pointer'
                        ]"
                    >
                        <span
                            :class="[
                                form.allow_procedure_booking ? 'translate-x-6' : 'translate-x-1',
                                'inline-block h-4 w-4 transform rounded-full bg-white transition'
                            ]"
                        />
                    </Switch>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
