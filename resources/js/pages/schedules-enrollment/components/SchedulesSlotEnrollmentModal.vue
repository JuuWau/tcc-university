<script setup lang="ts">
import {
	RefreshTableKey,
	ScheduleSlotEnrollmentKey,
} from '@/keys/schedule-enrollment/scheduleSlotEnrollmentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import { formatDateBr } from '@/src/utils/formatters';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const enrollmentModalInjected = inject(ScheduleSlotEnrollmentKey);
const loading = inject(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

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
		refreshTableRef?.value?.();
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
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Confirmar inscrição"
				subtitle="Revise o horário selecionado antes de confirmar."
			/>

			<div class="px-6 py-5">
				<div
					v-if="enrollmentModal.slot.value"
					class="rounded-lg border border-gray-200 bg-gray-50 p-4"
				>
					<div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
						<span class="font-medium text-gray-800">
							{{ formatDateBr(enrollmentModal.slot.value.date) }}
						</span>

						<span class="text-gray-500">
							{{ enrollmentModal.slot.value.start_time.slice(0, 5) }}
							-
							{{ enrollmentModal.slot.value.end_time.slice(0, 5) }}
						</span>
					</div>
				</div>

				<div
					class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4"
				>
					<p class="text-sm leading-relaxed text-amber-800">
						Após a confirmação, você estará vinculado a esse horário.
					</p>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="save"
				action-label="Confirmar inscrição"
				@cancel="close"
				@save="submit"
			/>
		</div>
	</div>
</template>