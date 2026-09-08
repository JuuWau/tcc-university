<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { PeriodDeleteKey, RefreshTableKey } from '@/keys/periods/periodKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(PeriodDeleteKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deleteModal || !loading) {
    throw new Error('PeriodDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function confirmDelete() {
    if (!deleteModal.period.value || loading.value) return;

    try {
        loading.value = true;

        await axios.delete(`/periods/${deleteModal.period.value.id}`);

		refreshTableRef?.value?.();

        toast.success('Período removido com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao remover período');
    } finally {
        loading.value = false;
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
				title="Excluir período"
				subtitle="Confirme a exclusão do período."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Tem certeza que deseja excluir este período?
				</p>

				<div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
					<p class="text-sm text-red-700">
						Esta ação não poderá ser desfeita.
					</p>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="delete"
				action-label="Excluir"
				@cancel="close"
				@delete="confirmDelete"
			/>
		</div>
	</div>
</template>
