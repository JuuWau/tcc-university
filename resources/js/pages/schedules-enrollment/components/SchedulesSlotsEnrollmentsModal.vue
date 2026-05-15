<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ScheduleSlotEnrollmentMultipleKey } from '@/keys/schedule-enrollment/scheduleSlotEnrollmentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import { formatDateBr } from '@/src/utils/formatters';

const enrollmentMultipleModalInjected = inject(ScheduleSlotEnrollmentMultipleKey);
const loading = inject(LoadingKey);

if (!enrollmentMultipleModalInjected) {
    throw new Error(
        'ScheduleSlotEnrollmentMultipleModal precisa estar dentro do provider',
    );
}

const enrollmentMultipleModal = enrollmentMultipleModalInjected;

    console.log(enrollmentMultipleModal.slots.value)
function close() {
    enrollmentMultipleModal.isOpen.value = false;
}

async function submit() {
    const slots = enrollmentMultipleModal.slots.value;

    if (!slots?.length || loading?.value) return;

    try {
        if (loading) loading.value = true;

        await axios.post('/schedule-enrollment/multiple-slots', {
            slot_ids: slots.map(slot => slot.id),
        });

        toast.success('Inscrição realizada com sucesso!');
        close();

        router.reload({
            preserveUrl: true,
        });
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao se inscrever');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="enrollmentMultipleModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-lg rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold">
                Confirmar inscrição
            </h2>

            <p class="mb-4 text-sm text-gray-600">
                Você está prestes a se inscrever nos horários selecionados.
            </p>

            <hr />

            <div class="my-4 space-y-3">
                <div class="text-sm text-gray-700">
                    <strong>Total de dias:</strong>
                    {{ enrollmentMultipleModal.slots.value.length }}
                </div>

                <div
                    class="max-h-40 overflow-y-auto rounded border border-gray-200"
                >
                    <div
                        v-for="slot in enrollmentMultipleModal.slots.value"
                        :key="slot.id"
                        class="flex justify-between border-b px-3 py-2 text-sm last:border-none"
                    >
                        <span>
                            {{ formatDateBr(slot.date) }}
                        </span>
                        <span class="text-gray-500">
                            {{ slot.start_time.slice(0,5) }} - {{ slot.end_time.slice(0,5) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-4 rounded bg-yellow-50 p-3 text-sm text-yellow-800">
                Após a confirmação, você estará vinculado a esses horários.
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton
                    label="Confirmar inscrição"
                    :loading="loading"
                    @click.stop="submit"
                />
            </div>
        </div>
    </div>
</template>
