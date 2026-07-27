<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ScheduleSlotEditMultipleKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { AppPageProps } from '@/types';
import { OpenClinicScheduleClinic, OpenClinicSchedulePeriodOption, OpenClinicScheduleResponsibleOption, OpenClinicScheduleRow, OpenClinicSchedulesFilters } from '@/types/schedule/openClinicSchedules';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, ref } from 'vue';
import { toast } from 'vue3-toastify';
import { scheduleSlotsUpdateSchema } from '@/schemas/scheduleSlotsUpdate.schema';
import { Switch } from '@headlessui/vue';
import AppMultiselect from '@/components/AppMultiselect.vue';

type Page = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    periods: OpenClinicSchedulePeriodOption[];
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;
const form = ref({
    responsible_ids: [] as number | [],
    start_time: '',
    end_time: '',
    available_slots: '' as string | number,
    allow_student_booking: false,
    allow_student_enrollment: false,
    allow_procedure_booking: false,
});

const editMultipleModalInjected = inject(ScheduleSlotEditMultipleKey);
const loading = inject(LoadingKey);

if (!editMultipleModalInjected) {
    throw new Error(
        'ScheduleSlotsEditModal precisa estar dentro do provider',
    );
}

const page = usePage<Page>();

const responsibleOptions = computed(() =>
    page.props.responsible.map((r) => ({ label: r.label, value: r.id })),
);

const editMultipleModal = editMultipleModalInjected;

    console.log(editMultipleModal.slots.value)
function close() {
    editMultipleModal.isOpen.value = false;
}

async function submit() {
    const slots = editMultipleModal.slots.value;
    if (!slots?.length || loading?.value) return;

    try {
        if (loading) loading.value = true;

        const payload = {
        ids: slots.map(slot => slot.id),
        slots_data: slots.map(slot => ({ 
                id: slot.id, 
                date: slot.date 
            })),
        ...form.value
        };

        const result = scheduleSlotsUpdateSchema.safeParse(payload);

        if (!result.success) {
        const errors = result.error.errors.map(e => e.message).join('\n');
        toast.error(errors);
        return;
        }

        const { slots_data, ...dataToSend } = result.data;
        
        await axios.put('/schedules/multiple-slots', dataToSend);
        toast.success('Agendas atualizadas com sucesso');
        close();

        router.reload({
        preserveUrl: true,
        });
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao editar agenda');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="editMultipleModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Editar agenda</h2>
            <hr />

            <div class="py-4 space-y-4">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Responsável
                    </label>
                    <AppMultiselect
                        v-model="form.responsible_ids"
                        :options="responsibleOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :multiple="true"
                        mode="tags"
                        :close-on-select="true"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Selecione o responsável"
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
                            type="text"
                            v-model="form.start_time"
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
                            type="text"
                            v-model="form.end_time"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Vagas disponíveis (*)
                    </label>
                    <input
                        type="number"
                        v-model="form.available_slots"
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
                            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition'
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
                            Ativar inscrição de alunos automaticamente
                        </p>
                        <p class="text-xs text-gray-500">
                            Se ativo, os alunos do período selecionado serão inscritos automaticamente, sem necessidade de inscrição manual.
                        </p>
                    </div>

                    <Switch
                        v-model="form.allow_student_enrollment"
                        :class="[
                            form.allow_student_enrollment ? 'bg-sky-600' : 'bg-gray-300',
                            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition'
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
