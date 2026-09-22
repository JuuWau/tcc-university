<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { ScheduleSlotCreateKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { scheduleSlotCreateSchema } from '@/schemas/scheduleSlotCreate.schema';
import { AppPageProps } from '@/types';
import {
    OpenClinicScheduleClinic,
    OpenClinicScheduleResponsibleOption,
    OpenClinicScheduleRow,
    OpenClinicSchedulesFilters,
} from '@/types/schedule/openClinicSchedules';
import { Switch } from '@headlessui/vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive } from 'vue';
import { toast } from 'vue3-toastify';

type Page = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;
const createModal = inject<any>(ScheduleSlotCreateKey);
const loadingInjected = inject(LoadingKey);
const page = usePage<Page>();

const responsibleOptions = computed(() =>
    page.props.responsible.map((r) => ({ label: r.label, value: r.id })),
);

const selectedPeriodId = computed(() => {
    const fromCreateModal = createModal?.periodId?.value ?? null;
    const fromFilters = page.props.filters?.period_id ?? null;
    return fromCreateModal ?? fromFilters ?? null;
});

if (!createModal) {
    throw new Error('ScheduleSlotCreateModal precisa estar dentro do provider');
}

if (!loadingInjected) {
    throw new Error('ScheduleSlotCreateModal precisa estar dentro do provider');
}

const loading = loadingInjected;

const form = reactive({
    date: '',
    responsible_ids: [] as number[],
    start_time: '',
    end_time: '',
    period_id: null as number | null,
    available_slots: null as number | null,
    allow_student_booking: false,
    allow_student_enrollment: false,
    allow_procedure_booking: false,
});

function close() {
    createModal.isOpen.value = false;
    form.date = '';
    form.responsible_ids = [];
    form.start_time = '';
    form.end_time = '';
    form.available_slots = null;
}

const responsibleLabel = computed(
    () =>
        responsibleOptions
            .filter((option) => form.responsible_ids.includes(option.value))
            .map((option) => option.label)
            .join(', ') || '—',
);

async function submit() {
    if (loading.value) return;
    console.log(selectedPeriodId.value);

    if (!selectedPeriodId.value) {
        toast.error('Selecione o período.');
        return;
    }

    const result = scheduleSlotCreateSchema.safeParse({
        ...form,
        period_id: selectedPeriodId.value,
    });
    console.log(result);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        await axios.post(
            `/schedules/open-clinics/${createModal.clinicId.value}`,
            result.data,
        );
        toast.success('Dia cadastrado com sucesso');
        router.reload({
            preserveUrl: true,
        });
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao cadastrar dia');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="createModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Cadastrar agenda"
                subtitle="Configure os horários e as opções de inscrição."
            />
            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <div class="space-y-4 py-5">
                    <AppMultiselect
                        v-model="form.responsible_ids"
                        :options="responsibleOptions"
                        field-label="Responsáveis"
                        label="label"
                        value-prop="value"
                        placeholder="Selecione os responsáveis"
                        mode="tags"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="true"
                        :append-to-body="true"
                    />
                    <BaseInput
                        v-model="form.date"
                        label="Data"
                        type="date"
                        :required="true"
                    />
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <BaseInput
                            v-model="form.start_time"
                            label="Início"
                            type="text"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            :required="true"
                        />
                        <BaseInput
                            v-model="form.end_time"
                            label="Fim"
                            type="text"
                            placeholder="HH:mm"
                            v-mask="'##:##'"
                            :required="true"
                        />
                    </div>
                    <BaseInput
                        v-model="form.available_slots"
                        label="Vagas disponíveis"
                        type="number"
                        min="0"
                        step="1"
                    />
                    <div
                        class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700">
                                Permitir inscrição de alunos
                            </p>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500"
                            >
                                Se desativado, os alunos não poderão se
                                inscrever nesses horários. A ocupação das vagas
                                deverá ser gerenciada manualmente pela equipe da
                                clínica.
                            </p>
                        </div>
                        <Switch
                            v-model="form.allow_student_booking"
                            :class="[
                                form.allow_student_booking
                                    ? 'bg-sky-600'
                                    : 'bg-gray-300',
                                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition',
                            ]"
                        >
                            <span
                                :class="[
                                    form.allow_student_booking
                                        ? 'translate-x-6'
                                        : 'translate-x-1',
                                    'inline-block h-4 w-4 transform rounded-full bg-white transition',
                                ]"
                            />
                        </Switch>
                    </div>
                    <div
                        class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700">
                                Ativar inscrição de alunos automaticamente
                            </p>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500"
                            >
                                Se ativo, os alunos do período selecionado serão
                                inscritos automaticamente, sem necessidade de
                                inscrição manual.
                            </p>
                        </div>
                        <Switch
                            v-model="form.allow_student_enrollment"
                            :class="[
                                form.allow_student_enrollment
                                    ? 'bg-sky-600'
                                    : 'bg-gray-300',
                                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition',
                            ]"
                        >
                            <span
                                :class="[
                                    form.allow_student_enrollment
                                        ? 'translate-x-6'
                                        : 'translate-x-1',
                                    'inline-block h-4 w-4 transform rounded-full bg-white transition',
                                ]"
                            />
                        </Switch>
                    </div>
                    <div
                        class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700">
                                Permitir registro de procedimento
                            </p>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500"
                            >
                                Se desativado, os alunos não poderão cadastrar
                                procedimentos no agendamento do paciente.
                            </p>
                        </div>
                        <Switch
                            v-model="form.allow_procedure_booking"
                            :class="[
                                form.allow_procedure_booking
                                    ? 'bg-sky-600'
                                    : 'bg-gray-300',
                                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition',
                            ]"
                        >
                            <span
                                :class="[
                                    form.allow_procedure_booking
                                        ? 'translate-x-6'
                                        : 'translate-x-1',
                                    'inline-block h-4 w-4 transform rounded-full bg-white transition',
                                ]"
                            />
                        </Switch>
                    </div>
                </div>
            </div>
            <FormFooter
                :loading="loading"
                action-label="Salvar"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>
