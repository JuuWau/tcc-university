<script setup lang="ts">
import { RefreshTableKey, ScheduleSlotEnrollmentMultipleKey } from '@/keys/schedule-enrollment/scheduleSlotEnrollmentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import { formatDateBr } from '@/src/utils/formatters';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const enrollmentMultipleModalInjected = inject(ScheduleSlotEnrollmentMultipleKey);
const loading = inject(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

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
		refreshTableRef?.value?.();
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
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Confirmar inscrição"
				subtitle="Revise os horários selecionados antes de confirmar a inscrição."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-5 py-5">
					<div
						class="rounded-lg border border-gray-200 bg-gray-50 p-4"
					>
						<p class="text-sm font-medium text-gray-800">
							Total de dias
						</p>

						<p class="mt-1 text-2xl font-semibold text-sky-600">
							{{ enrollmentMultipleModal.slots.value.length }}
						</p>
					</div>

					<div>
						<p class="mb-2 text-sm font-medium text-gray-700">
							Horários selecionados
						</p>

						<div
							class="max-h-56 overflow-y-auto rounded-lg border border-gray-200"
						>
							<div
								v-for="slot in enrollmentMultipleModal.slots.value"
								:key="slot.id"
								class="flex items-center justify-between gap-4 border-b border-gray-100 px-3 py-2.5 text-sm last:border-b-0"
							>
								<span class="font-medium text-gray-700">
									{{ formatDateBr(slot.date) }}
								</span>

								<span class="shrink-0 text-gray-500">
									{{ slot.start_time.slice(0, 5) }}
									-
									{{ slot.end_time.slice(0, 5) }}
								</span>
							</div>
						</div>
					</div>

					<div
						class="rounded-lg border border-amber-200 bg-amber-50 p-4"
					>
						<p class="text-sm leading-relaxed text-amber-800">
							Após a confirmação, você estará vinculado a todos os
							horários selecionados.
						</p>
					</div>
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