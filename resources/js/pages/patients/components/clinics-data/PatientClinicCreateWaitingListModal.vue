<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import AppMultiselect from '@/components/AppMultiselect.vue';

import {
PatientClinicCreateWaitingListKey,
    RefreshTableKey,
} from '@/keys/patients/patientClinicsKeys';

import { LoadingKey } from '@/keys/ui/loadingKey';

import axios from 'axios';
import { inject, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

type ClinicOption = {
    label: string;
    value: number;
};

const modal = inject(PatientClinicCreateWaitingListKey);
const loading = inject<any>(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

if (!modal) {
    throw new Error(
        'PatientClinicEnrollModal precisa estar dentro do provider'
    );
}

const clinicOptions = ref<ClinicOption[]>([]);
const selectedClinic = ref<ClinicOption | null>(null);
const loadingClinics = ref(false);

watch(
    () => modal.isOpen.value,
    async (isOpen: boolean) => {
        if (!isOpen) {
            return;
        }

        selectedClinic.value = null;
        console.log('ola')
        await loadClinics();
    }
);

async function loadClinics() {
    try {
        loadingClinics.value = true;

        const { data } = await axios.get(
            `/patients/${modal.patient.value.id}/available-clinics`
        );

        clinicOptions.value = data ?? [];
    } catch {
        toast.error(
            'Erro ao carregar clínicas'
        );
    } finally {
        loadingClinics.value = false;
    }
}

function close() {
    modal.isOpen.value = false;
    modal.patient.value = null;
    selectedClinic.value = null;
}

async function submit() {
    if (!selectedClinic.value) {
        toast.error(
            'Selecione uma clínica'
        );

        return;
    }

    try {
        loading.value = true;
        
        await axios.post(`/patients/clinics/${selectedClinic.value}/waiting-list`,
                {
                        patient_id: modal.patient.value.id,
                }
        );

        toast.success(
            'Paciente inscrito com sucesso'
        );

        refreshTableRef?.value?.();

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao inscrever paciente'
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="modal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow-xl"
		>
			<FormHeader
				title="Inscrever paciente"
				subtitle="Selecione a clínica para inscrição."
			/>

			<div class="px-6 py-5">
				<AppMultiselect
					v-model="selectedClinic"
					:options="clinicOptions"
					field-label="Clínica"
					label="label"
					value-prop="value"
					:loading="loadingClinics"
					:searchable="true"
					:can-clear="true"
					:close-on-select="true"
					:append-to-body="true"
					placeholder="Selecione uma clínica"
				/>
			</div>

			<FormFooter
				:loading="loading"
				@cancel="close"
				@save="submit"
			/>
		</div>
	</div>
</template>