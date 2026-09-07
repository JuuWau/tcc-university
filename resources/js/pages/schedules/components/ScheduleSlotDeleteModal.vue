<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { ScheduleSlotDeleteKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import { formatDateBr } from '@/src/utils/formatters';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const deleteModal = inject(ScheduleSlotDeleteKey);
const loading = inject(LoadingKey);

if (!deleteModal) {
    throw new Error('ScheduleSlotDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function submit() {
    const row = deleteModal.row.value;
    if (!row || loading?.value) return;

    try {
        if (loading) loading.value = true;
        await axios.delete(`/schedules/slots/${row.id}`);
        toast.success('Agenda excluída com sucesso');
        close();
        router.reload({ preserveUrl: true });
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao excluir agenda');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="deleteModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Excluir agenda"
				subtitle="Confirme a exclusão do horário selecionado."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Tem certeza que deseja excluir o horário
					<strong class="font-semibold text-gray-900">
						{{
							deleteModal.row?.value?.date
								? formatDateBr(deleteModal.row.value.date)
								: ''
						}}
					</strong>
					(
					<strong class="font-semibold text-gray-900">
						{{ deleteModal.row?.value?.start_time?.slice(0, 5) }}
						às
						{{ deleteModal.row?.value?.end_time?.slice(0, 5) }}
					</strong>
					)?
				</p>

				<div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
					<p class="mb-2 text-sm font-medium text-red-800">
						Esta ação é irreversível e irá:
					</p>

					<ul class="space-y-2 text-sm text-red-700">
						<li class="flex gap-2">
							<span>•</span>
							<span>Remover o slot da agenda.</span>
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
				action-label="Excluir agenda"
				@cancel="close"
				@delete="submit"
			/>
		</div>
	</div>
</template>
