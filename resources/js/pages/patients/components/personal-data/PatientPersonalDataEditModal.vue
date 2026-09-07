<script setup lang="ts">
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import type { PatientForTab } from '@/types/patient/patient';
import axios from 'axios';
import { computed, inject, ref } from 'vue';
import { toast } from 'vue3-toastify';

import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import PatientPersonalDataEditForm from '@/components/form/patient/PatientPersonalDataEditForm.vue';

const context = inject(PatientTabContextKey);

if (!context) {
	throw new Error(
		'PatientPersonalDataEditModal must be used inside PatientTab',
	);
}

const patient = computed(() => context.patient.value);
const editPersonalDataModalOpen = context.editPersonalDataModalOpen;

const emit = defineEmits<{
	updated: [];
}>();

const loading = ref(false);

const formRef =
	ref<InstanceType<typeof PatientPersonalDataEditForm> | null>(null);

function close() {
	editPersonalDataModalOpen.value = false;
}

async function submit(data: {
	name: string;
	email: string;
	phone: string;
	cpf: string;
	birth_date: string;
	biological_sex: 'male' | 'female' | null;
	patient_type: 'adulto' | 'pediatria' | null;
	cep: string;
	street: string;
	neighborhood: string;
	number: string;
	complement: string | null;
	city: string;
	state: string;
}) {
	if (loading.value || !patient.value) return;

	try {
		loading.value = true;

		const { data: response } = await axios.patch<{
			message: string;
			patient: PatientForTab;
		}>(
			`/patients/${patient.value.id}`,
			data,
		);

		toast.success(
			response.message ?? 'Dados atualizados com sucesso',
		);

		emit('updated');
		close();
	} catch (err: any) {
		toast.error(
			err.response?.data?.message ??
				'Erro ao atualizar dados do paciente',
		);
	} finally {
		loading.value = false;
	}
}
</script>

<template>
	<div
		v-if="editPersonalDataModalOpen"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-2"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white"
		>
			<FormHeader
				title="Editar dados do paciente"
				subtitle="Atualize os dados pessoais e o endereço do paciente."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto ">
				<PatientPersonalDataEditForm
					ref="formRef"
					:patient="patient"
					@submit="submit"
				/>
			</div>

			<FormFooter
				:loading="loading"
				@cancel="close"
				@save="formRef?.submit()"
			/>
		</div>
	</div>
</template>