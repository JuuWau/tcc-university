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

type Page = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    periods: OpenClinicSchedulePeriodOption[];
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;
const form = ref({
    responsible_id: null as number | null,
    start_time: '',
    end_time: '',
    available_slots: '' as string | number,
});

const editMultipleModalInjected = inject(ScheduleSlotEditMultipleKey);
const loading = inject(LoadingKey);

if (!editMultipleModalInjected) {
    throw new Error(
        'ScheduleSlotsDeleteModal precisa estar dentro do provider',
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
        ...form.value
        };

        const result = scheduleSlotsUpdateSchema.safeParse(payload);

        if (!result.success) {
        const errors = result.error.errors.map(e => e.message).join('\n');
        toast.error(errors);
        return;
        }

        console.log(result.data)
        await axios.put('/schedules/multiple-slots', result.data);
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
                    <Multiselect
                        :options="responsibleOptions"
                        v-model="form.responsible_id"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        placeholder="Responsável"
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
                            type="time"
                             v-model="form.start_time"
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
                            type="time"
                            v-model="form.end_time"
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
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
