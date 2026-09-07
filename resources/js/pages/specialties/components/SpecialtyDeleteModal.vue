<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import {
	RefreshTableKey,
	SpecialtyDeleteKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(SpecialtyDeleteKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deleteModal || !loading) {
    throw new Error('SpecialtyDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function confirmDelete() {
    if (!deleteModal.specialty.value || loading.value) return;

    try {
        loading.value = true;

        await axios.delete(`/specialties/${deleteModal.specialty.value.id}`);

		refreshTableRef?.value?.();

        toast.success('Especialidade removida com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao remover especialidade',
        );
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
				title="Excluir especialidade"
				subtitle="Confirme a exclusão da especialidade."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Tem certeza que deseja excluir a especialidade
					<strong class="font-semibold text-gray-900">
						{{ deleteModal.specialty.value?.name }}
					</strong>
					?
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
