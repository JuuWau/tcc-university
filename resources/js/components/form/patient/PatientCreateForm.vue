<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';

defineProps<{
	studentsOptions: {
		label: string;
		value: number;
	}[];
	stateOptions: {
		label: string;
		value: string;
	}[];
	cityOptions: {
		label: string;
		value: string;
	}[];
}>();

const model = defineModel<{
	code: string | null;
	name: string | null;
	email: string | null;
	student_ids: number[];
	cpf: string | null;
	phone: string | null;
	birth_date: string | null;
	biological_sex: 'male' | 'female' | null;
	cep: string | null;
	street: string | null;
	neighborhood: string | null;
	number: string | null;
	complement: string | null;
	city: string | null;
	state: string | null;
	patient_type: 'adulto' | 'pediatria' | null;
}>();

const patientTypeOptions = [
	{ label: 'Adulto', value: 'adulto' },
	{ label: 'Pediatria', value: 'pediatria' },
];

const biologicalSexOptions = [
	{ label: 'Masculino', value: 'male' },
	{ label: 'Feminino', value: 'female' },
];
</script>

<template>
	<div class="space-y-4 py-4">
		<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
			<BaseInput
				v-model="model.code"
				label="Código (*)"
				type="text"
				maxlength="50"
				placeholder="Código do paciente"
			/>

			<BaseInput
				v-model="model.name"
				label="Nome completo (*)"
				type="text"
				maxlength="255"
				placeholder="Nome do paciente"
			/>
		</div>

		<div>
			<BaseInput
				v-model="model.email"
				label="Email"
				type="email"
				maxlength="255"
				placeholder="email@exemplo.com"
			/>
		</div>

		<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
			<BaseInput
				v-model="model.cpf"
				label="CPF"
				type="text"
				maxlength="14"
				v-mask="'###.###.###-##'"
				placeholder="000.000.000-00"
			/>

			<BaseInput
				v-model="model.phone"
				label="Telefone"
				type="tel"
				v-mask="'(##) #####-####'"
				placeholder="(99) 99999-9999"
			/>
		</div>

		<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
			<BaseInput
				v-model="model.birth_date"
				label="Data de nascimento"
				type="date"
			/>

			<AppMultiselect
				v-model="model.biological_sex"
				:options="biologicalSexOptions"
				field-label="Sexo biológico"
				value-prop="value"
				:searchable="false"
				:close-on-select="true"
				:can-clear="true"
				:append-to-body="true"
				placeholder="Selecione o sexo"
			/>
		</div>

		<div>
			<AppMultiselect
				v-model="model.patient_type"
				:options="patientTypeOptions"
				field-label="Tipo de paciente"
				value-prop="value"
				:searchable="false"
				:close-on-select="true"
				:can-clear="false"
				:append-to-body="true"
				placeholder="Selecione o tipo"
			/>
		</div>

		<div class="pb-4">
			<AppMultiselect
				v-model="model.student_ids"
				:options="studentsOptions"
				mode="tags"
				field-label="Estudantes"
				value-prop="value"
				:append-to-body="true"
				:searchable="true"
				:close-on-select="false"
				:can-clear="true"
				placeholder="Escolha os estudantes"
			/>
		</div>

		<div class="border-t border-gray-200 pt-4">
			<h3 class="mb-3 text-sm font-semibold text-gray-700">
				Endereço (opcional)
			</h3>

			<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
				<BaseInput
					v-model="model.cep"
					label="CEP"
					type="text"
					maxlength="9"
					v-mask="'#####-###'"
					placeholder="00000-000"
				/>

				<div class="md:col-span-2">
					<BaseInput
						v-model="model.street"
						label="Rua"
						type="text"
						maxlength="100"
						placeholder="Logradouro"
					/>
				</div>
			</div>

			<div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
				<BaseInput
					v-model="model.number"
					label="Número"
					type="text"
					maxlength="10"
					placeholder="Número"
				/>

				<BaseInput
					v-model="model.neighborhood"
					label="Bairro"
					type="text"
					maxlength="50"
					placeholder="Bairro"
				/>

				<BaseInput
					v-model="model.complement"
					label="Complemento"
					type="text"
					maxlength="50"
					placeholder="Complemento"
				/>
			</div>

			<div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
				<AppMultiselect
					v-model="model.state"
					:options="stateOptions"
					field-label="Estado"
					value-prop="value"
					:append-to-body="true"
					:searchable="true"
					:close-on-select="true"
					:can-clear="true"
					placeholder="Selecione o Estado"
				/>

				<AppMultiselect
					v-model="model.city"
					:options="cityOptions"
					field-label="Cidade"
					value-prop="value"
					:append-to-body="true"
					:searchable="true"
					:close-on-select="true"
					:can-clear="true"
					placeholder="Selecione a Cidade"
				/>
			</div>
		</div>
	</div>
</template>
