<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { PatientDeactivateKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import DeactivateButton from '@/components/buttons/DeactivateButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const deactivateModal = inject(PatientDeactivateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deactivateModal || !loading) {
    throw new Error('PatientDeactivateModal precisa estar dentro do provider');
}

function close() {
    deactivateModal.isOpen.value = false;
    deactivateModal.patient.value = null;
}

async function confirmDeactivate() {
    const patient = deactivateModal.patient.value;
    if (!patient || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/patients/deactivate/${patient.id}`);
        toast.success('Paciente inativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao inativar paciente',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="deactivateModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Inativar paciente"
				subtitle="Confirme a inativação do paciente."
			/>

			<div class="px-6 py-5">
				<p class="text-sm text-gray-600">
					Tem certeza que deseja inativar o paciente
					<strong class="font-semibold text-gray-900">
						{{
							deactivateModal.patient.value?.name ??
							deactivateModal.patient.value?.email
						}}
					</strong>
					?
				</p>
			</div>

			<FormFooter
				:loading="loading"
				action="deactivate"
				action-label="Inativar"
				@cancel="close"
				@deactivate="confirmDeactivate"
			/>
		</div>
	</div>
</template>
