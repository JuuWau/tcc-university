<script setup lang="ts">
import { inject, reactive } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';

import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';

import { ClinicEnrollKey, RefreshTableKey } from '@/keys/clinics-management/clinicManagementShowKeys';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const enrollModal = inject<any>(ClinicEnrollKey);
const refreshTableRef = inject(RefreshTableKey);

if (!enrollModal) {
    throw new Error(
        'ClinicsEnrollModal precisa estar dentro do provider'
    );
}

function close() {
    enrollModal.isOpen.value = false;
    enrollModal.patient.value = null;
}

async function submit() {
    if (!enrollModal.patient.value) return;

    try {
        await axios.post(
            `/clinics-management/${enrollModal.clinicId.value}/enroll`,
            {
                patient_id: enrollModal.patient.value.patient_id,
            }
        );

        toast.success(
            'Paciente inscrito com sucesso!'
        );
        refreshTableRef?.value?.();
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao inscrever paciente.'
        );
    }
}
</script>

<template>
	<div
		v-if="enrollModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Inscrever paciente"
				subtitle="Confirme a inscrição do paciente na clínica."
			/>

			<div class="px-6 py-5">
				<p class="text-sm leading-relaxed text-gray-600">
					Deseja mesmo inscrever
					<strong class="font-semibold text-gray-900">
						{{ enrollModal.patient.value?.name }}
					</strong>
					na clínica?
				</p>
			</div>

			<FormFooter
				action="save"
				action-label="Inscrever"
				@cancel="close"
				@save="submit"
			/>
		</div>
	</div>
</template>