<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { PrePatientEditKey, RefreshTableKey, } from '@/keys/pre-patients/prePatientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { prePatientUpdateSchema } from '@/schemas/prePatientUpdate.schema';
import type { PrePatient } from '@/types/pre-patients/prePatient';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

const loading = inject(LoadingKey);
const modal = inject(PrePatientEditKey);
const refreshTableRef = inject(RefreshTableKey);

if (!modal || !loading) {
	throw new Error(
		'PrePatientEditModal precisa estar dentro do provider',
	);
}

const prePatient = computed(
	() => modal.prePatient.value,
);

const form = reactive({
	name: '',
	cpf: '',
	birth_date: '',
	biological_sex: null as 'male' | 'female' | null,
	phone: '',
	email: '',
	patient_type: null as 'adult' | 'pediatric' | null,
});

const biologicalSexOptions = [
	{
		label: 'Feminino',
		value: 'female',
	},
	{
		label: 'Masculino',
		value: 'male',
	},
];

const patientTypeOptions = [
	{
		label: 'Adulto',
		value: 'adult',
	},
	{
		label: 'Pediatria',
		value: 'pediatric',
	},
];

watch(
	prePatient,
	(value: PrePatient | null) => {
		if (!value) {
			return;
		}

		form.name = value.name ?? '';
		form.cpf = value.cpf ?? '';
		form.birth_date = value.birth_date ?? '';
		form.biological_sex = value.biological_sex as
			| 'male'
			| 'female'
			| null;
		form.phone = value.phone ?? '';
		form.email = value.email ?? '';
		form.patient_type = value.patient_type as
			| 'adult'
			| 'pediatric'
			| null;
	},
	{ immediate: true },
);

function close() {
	modal.isOpen.value = false;
}

async function save() {
	if (!prePatient.value?.id || loading.value) {
		return;
	}

        const result = prePatientUpdateSchema.safeParse(form);

	if (!result.success) {
		toast.error(result.error.issues[0].message);
		return;
	}

	try {
		loading.value = true;

		const response = await axios.put(
			`/pre-patients/${prePatient.value.id}`,
			result.data,
		);

		toast.success(response.data.message);

		close();

		setTimeout(() => {
			refreshTableRef?.value?.();
		}, 0);
	} catch (error: any) {
		toast.error(
			error.response?.data?.message ??
				'Erro ao atualizar pré-paciente',
		);
	} finally {
		loading.value = false;
	}
}
</script>

<template>
	<div
		v-if="modal.isOpen.value && prePatient"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar Pré-Paciente"
				subtitle="Atualize os dados do pré-paciente."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div
					class="grid grid-cols-1 gap-4 py-6 sm:grid-cols-2"
				>
					<div class="sm:col-span-2">
						<BaseInput
							v-model="form.name"
							label="Nome"
							placeholder="Digite o nome completo"
							required
						/>
					</div>

					<BaseInput
						v-model="form.cpf"
						label="CPF"
						placeholder="000.000.000-00"
                                                v-mask="'###.###.###-##'"
					/>

					<BaseInput
						v-model="form.birth_date"
						label="Data de nascimento"
						type="date"
					/>

					<BaseInput
						v-model="form.phone"
						label="Telefone"
                                                v-mask="'(##) #####-####'"
						placeholder="(00) 00000-0000"
					/>

					<BaseInput
						v-model="form.email"
						label="E-mail"
						type="email"
						placeholder="email@exemplo.com"
					/>

					<AppMultiselect
						v-model="form.biological_sex"
						field-label="Sexo biológico"
						:options="biologicalSexOptions"
						label="label"
						value-prop="value"
						placeholder="Selecione"
						:can-clear="false"
						required
						:append-to-body="true"
					/>

					<AppMultiselect
						v-model="form.patient_type"
						field-label="Tipo de paciente"
						:options="patientTypeOptions"
						label="label"
						value-prop="value"
						placeholder="Selecione"
						:can-clear="false"
						required
						:append-to-body="true"
					/>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="save"
				action-label="Salvar"
				@cancel="close"
				@save="save"
			/>
		</div>
	</div>
</template>