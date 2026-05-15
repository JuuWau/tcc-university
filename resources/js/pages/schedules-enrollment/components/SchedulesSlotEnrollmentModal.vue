<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ScheduleSlotEnrollmentKey } from '@/keys/schedule-enrollment/scheduleSlotEnrollmentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import { formatDateBr } from '@/src/utils/formatters';

const enrollmentModalInjected = inject(ScheduleSlotEnrollmentKey);
const loading = inject(LoadingKey);

if (!enrollmentModalInjected) {
    throw new Error(
        'ScheduleSlotEnrollmentModal precisa estar dentro do provider',
    );
}

const enrollmentModal = enrollmentModalInjected;

function close() {
    enrollmentModal.isOpen.value = false;
}

async function submit() {
    const slot = enrollmentModal.slot.value;

    if (!slot || loading?.value) return;

    try {
        if (loading) loading.value = true;

        await axios.post('/schedule-enrollment/student-enroll', {
            slot_id: slot.id,
        });

        toast.success('Inscrição realizada com sucesso!');

        close();

        router.reload({
            preserveUrl: true,
        });
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao se inscrever',
        );
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="enrollmentModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-lg rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold">
                Confirmar inscrição
            </h2>

            <p class="mb-4 text-sm text-gray-600">
                Você está prestes a se inscrever no horário selecionado.
            </p>

            <hr />

            <div
                v-if="enrollmentModal.slot.value"
                class="my-4"
            >
                <div
                    class="flex justify-between rounded border border-gray-200 px-3 py-2 text-sm"
                >
                    <span>
                        {{
                            formatDateBr(
                                enrollmentModal.slot.value.date
                            )
                        }}
                    </span>

                    <span class="text-gray-500">
                        {{
                            enrollmentModal.slot.value.start_time.slice(0, 5)
                        }}
                        -
                        {{
                            enrollmentModal.slot.value.end_time.slice(0, 5)
                        }}
                    </span>
                </div>
            </div>

            <div class="mb-4 rounded bg-yellow-50 p-3 text-sm text-yellow-800">
                Após a confirmação, você estará vinculado a esse horário.
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