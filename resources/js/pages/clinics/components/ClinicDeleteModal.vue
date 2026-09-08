<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { ClinicDeleteKey, RefreshTableKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(ClinicDeleteKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deleteModal || !loading) {
    throw new Error('ClinicDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function submit() {
    if (!deleteModal.clinic.value || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/clinics/${deleteModal.clinic.value.id}`);
		refreshTableRef?.value?.();
        toast.success('Clínica removida com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao excluir clínica');
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
				title="Excluir clínica"
				subtitle="Confirme a exclusão da clínica."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Tem certeza que deseja excluir
					<strong class="font-semibold text-gray-900">
						{{ deleteModal.clinic?.name }}
					</strong>
					?
				</p>

				<div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
					<p class="text-sm leading-relaxed text-red-700">
						A exclusão será bloqueada caso exista histórico de agendas
						realizadas para esta clínica.
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
