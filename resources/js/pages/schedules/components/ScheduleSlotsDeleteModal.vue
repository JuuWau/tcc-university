<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { ScheduleSlotDeleteMultipleKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteMultipleModalInjected = inject(ScheduleSlotDeleteMultipleKey);
const loading = inject(LoadingKey);

if (!deleteMultipleModalInjected) {
    throw new Error(
        'ScheduleSlotsDeleteModal precisa estar dentro do provider',
    );
}

const deleteMultipleModal = deleteMultipleModalInjected;

function close() {
    deleteMultipleModal.isOpen.value = false;
}

async function submit() {
    const slots = deleteMultipleModal.slots.value;
    if (!slots?.length || loading?.value) return;
    try {
        if (loading) loading.value = true;
        await axios.delete(
            `/schedules/multiple-slots/`, {
                data: {
                    ids: slots.map(slot => slot.id)
                }
            });
        toast.success('Agenda excluída com sucesso');
        close();
        router.reload({
            preserveUrl: true,
        } as Parameters<typeof router.reload>[0]);
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao excluir agenda');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="deleteMultipleModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Excluir agendas"
				subtitle="Confirme a exclusão dos horários selecionados."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Tem certeza que deseja excluir os horários selecionados?
				</p>

				<div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
					<p class="mb-2 text-sm font-medium text-red-800">
						Esta ação é irreversível e irá:
					</p>

					<ul class="space-y-2 text-sm text-red-700">
						<li class="flex gap-2">
							<span>•</span>
							<span>Remover os slots das agendas.</span>
						</li>

						<li class="flex gap-2">
							<span>•</span>
							<span>
								Cancelar todas as inscrições dos alunos.
							</span>
						</li>

						<li class="flex gap-2">
							<span>•</span>
							<span>
								Cancelar todos os agendamentos vinculados a esses
								alunos.
							</span>
						</li>
					</ul>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="delete"
				action-label="Excluir agendas"
				@cancel="close"
				@delete="submit"
			/>
		</div>
	</div>
</template>
