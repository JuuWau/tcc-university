<script setup lang="ts">
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
import { router, usePage } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
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
        responsible_id: form.responsible_id,
        date: form.date,
        start_time: form.start_time,
        end_time: form.end_time,
        available_slots: form.available_slots,
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;
        await axios.patch(`/schedules/slots/${row.id}`, {
            responsible_id: result.data.responsible_id,
            date: result.data.date,
            start_time: result.data.start_time,
            end_time: result.data.end_time,
            available_slots: result.data.available_slots,
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
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
