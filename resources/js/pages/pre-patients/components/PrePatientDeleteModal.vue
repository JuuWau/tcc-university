<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { PrePatientDeleteKey, RefreshTableKey } from '@/keys/pre-patients/prePatientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(PrePatientDeleteKey);

const refreshTableRef = inject(RefreshTableKey);

const loading = inject(LoadingKey);

if (!deleteModal || !loading) {
	throw new Error(
		'PrePatientDeleteModal precisa estar dentro do provider',
	);
}

function close() {
	deleteModal.isOpen.value = false;
}

async function submit() {
	if (!deleteModal.prePatient.value || loading.value) {
		return;
	}

	try {
		loading.value = true;

		await axios.delete(
			`/pre-patients/${deleteModal.prePatient.value.id}`,
		);

		close();

		toast.success('Pré-paciente removido com sucesso');

		setTimeout(() => {
			refreshTableRef?.value?.();
		}, 0);
	} catch (error: any) {
		toast.error(
			error.response?.data?.message ??
				'Erro ao excluir pré-paciente',
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
				title="Excluir pré-paciente"
				subtitle="Confirme a exclusão do pré-paciente."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Tem certeza que deseja excluir
					<strong class="font-semibold text-gray-900">
						{{ deleteModal.prePatient?.value?.name }}
					</strong>
					?
				</p>

				<div
					class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4"
				>
					<p class="text-sm leading-relaxed text-red-700">
						A exclusão será bloqueada caso o pré-paciente
						possua vínculos com clínicas.
					</p>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="delete"
				action-label="Excluir"
				@cancel="close"
				@delete="submit"
			/>
		</div>
	</div>
</template>
