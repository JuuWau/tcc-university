<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
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
import { usePage } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
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
    responsible_id: null as number | null,
    start_time: '',
    end_time: '',
    period_id: null as number | null,
    available_slots: null as number | null,
    allow_student_booking: true,
    allow_student_enrollment: true,
});

function close() {
    createModal.isOpen.value = false;
    form.date = '';
    form.responsible_id = null;
    form.start_time = '';
    form.end_time = '';
    form.available_slots = null;
}

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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div
            class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">Cadastrar agenda</h2>
            <hr />

            <div class="space-y-4 py-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Responsável
                    </label>
                    <Multiselect
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
                            type="time"
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
                            type="time"
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
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
