<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { patientPersonalDataEditSchema } from '@/schemas/patientPersonalDataEdit.schema';
import type { PatientForTab } from '@/types/patient/patient';
import { computed, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
	patient: PatientForTab | null;
}>();

const emit = defineEmits<{
	submit: [
		data: {
			name: string;
			email: string;
			phone: string;
			cpf: string;
			birth_date: string;
			patient_type: 'adulto' | 'pediatria' | null;
			biological_sex: 'male' | 'female' | null;
			cep: string;
			street: string;
			neighborhood: string;
			number: string;
			complement: string | null;
			city: string;
			state: string;
		},
	];
}>();

const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);

const viaCep = ViaCep();

const patientTypeOptions = [
	{ label: 'Adulto', value: 'adulto' },
	{ label: 'Pediatria', value: 'pediatria' },
];

const biologicalSexOptions = [
	{ label: 'Masculino', value: 'male' },
	{ label: 'Feminino', value: 'female' },
];

const stateOptions = computed(() =>
	states.value.map((state) => ({
		label: state.nome,
		value: state.sigla,
	})),
);

const cityOptions = computed(() =>
	cities.value.map((city) => ({
		label: city.nome,
		value: city.nome,
	})),
);

const form = reactive({
	name: '',
	email: '',
	phone: '',
	cpf: '',
	birth_date: '',
	patient_type: null as 'adulto' | 'pediatria' | null,
	biological_sex: null as 'male' | 'female' | null,
	cep: '',
	street: '',
	neighborhood: '',
	number: '',
	complement: null as string | null,
	city: '',
	state: '',
});

function formatDateForInput(
	dateStr: string | null | undefined,
): string {
	if (!dateStr) return '';

	const date = new Date(dateStr);

	if (Number.isNaN(date.getTime())) {
		return '';
	}

	return date.toISOString().slice(0, 10);
}

function populateForm() {
	const patient = props.patient;
	const address = patient?.address;

	form.name = patient?.name ?? '';
	form.email = patient?.email ?? '';
	form.phone = patient?.phone ?? '';
	form.cpf = patient?.cpf ?? '';
	form.birth_date = formatDateForInput(patient?.birth_date);
	form.patient_type = patient?.patient_type ?? null;
	form.biological_sex = patient?.biological_sex ?? null;

	form.cep = address?.cep ?? '';
	form.street = address?.street ?? '';
	form.neighborhood = address?.neighborhood ?? '';
	form.number = address?.number ?? '';
	form.complement = address?.complement ?? null;
	form.city = address?.city ?? '';
	form.state = address?.state ?? '';
}

watch(
	() => props.patient,
	(patient) => {
		if (!patient) return;

		populateForm();
	},
	{ immediate: true },
);

async function loadStates() {
	if (states.value.length) return;

	states.value = await IbgeService.getUfData();
}

void loadStates();

watch(
	() => form.cep,
	async (newCep) => {
		const cepClean = newCep?.replace(/\D/g, '');

		if (!cepClean || cepClean.length !== 8) {
			return;
		}

		const data = await viaCep.getCepData(cepClean);

		if (!data) return;

		form.street = data.logradouro ?? '';
		form.neighborhood = data.bairro ?? '';
		form.city = data.localidade ?? '';
		form.state = data.uf ?? '';

		if (data.uf) {
			cities.value = await IbgeService.getCityData(data.uf);
		}
	},
);

watch(
	() => form.state,
	async (newState) => {
		if (!newState) {
			cities.value = [];
			form.city = '';
			return;
		}

		cities.value = await IbgeService.getCityData(newState);

		if (!cities.value.some((city) => city.nome === form.city)) {
			form.city = '';
		}
	},
);

function submit() {
	const result = patientPersonalDataEditSchema.safeParse({
		name: form.name,
		email: form.email,
		phone: form.phone,
		cpf: form.cpf,
		birth_date: form.birth_date,
		patient_type: form.patient_type,
		biological_sex: form.biological_sex,
		cep: form.cep,
		street: form.street,
		neighborhood: form.neighborhood,
		number: form.number,
		complement: form.complement,
		city: form.city,
		state: form.state,
	});

	if (!result.success) {
		toast.error(result.error.issues[0].message);
		return;
	}

	emit('submit', result.data);
}

defineExpose({
	submit,
});
</script>

<template>
	<form
		class="min-h-0 flex-1 overflow-y-auto px-6 py-4"
		@submit.prevent="submit"
	>
		<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
			<BaseInput
				v-model="form.name"
				label="Nome completo (*)"
				type="text"
				maxlength="255"
				placeholder="Nome completo"
			/>

			<BaseInput
				v-model="form.email"
				label="E-mail"
				type="email"
				placeholder="email@exemplo.com"
			/>
		</div>

		<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
			<BaseInput
				v-model="form.phone"
				label="Telefone"
				type="tel"
				v-mask="'(##) #####-####'"
				placeholder="(99) 99999-9999"
			/>

			<BaseInput
				v-model="form.cpf"
				label="CPF"
				type="text"
				maxlength="14"
				v-mask="'###.###.###-##'"
				placeholder="000.000.000-00"
			/>
		</div>

		<div class="mt-4">
			<BaseInput
				v-model="form.birth_date"
				label="Data de nascimento"
				type="date"
			/>
		</div>

		<div class="mt-4">
			<AppMultiselect
				v-model="form.biological_sex"
				:options="biologicalSexOptions"
				field-label="Sexo biológico (*)"
				label="label"
				value-prop="value"
				:searchable="false"
				:close-on-select="true"
				:can-clear="false"
				:append-to-body="true"
				placeholder="Selecione o sexo"
			/>
		</div>

		<div class="mt-4">
			<AppMultiselect
				v-model="form.patient_type"
				:options="patientTypeOptions"
				field-label="Tipo de atendimento (*)"
				label="label"
				value-prop="value"
				:searchable="false"
				:close-on-select="true"
				:can-clear="false"
				:append-to-body="true"
				placeholder="Selecione o tipo"
			/>
		</div>

		<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
			<BaseInput
				v-model="form.cep"
				label="CEP (*)"
				type="text"
				maxlength="9"
				v-mask="'#####-###'"
				placeholder="00000-000"
			/>

			<div class="md:col-span-2">
				<BaseInput
					v-model="form.street"
					label="Endereço (*)"
					type="text"
					maxlength="100"
					placeholder="Logradouro"
				/>
			</div>
		</div>

		<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
			<BaseInput
				v-model="form.neighborhood"
				label="Bairro (*)"
				type="text"
				maxlength="50"
				placeholder="Bairro"
			/>

			<BaseInput
				v-model="form.number"
				label="Número (*)"
				type="text"
				maxlength="5"
				placeholder="Número"
			/>

			<BaseInput
				v-model="form.complement"
				label="Complemento"
				type="text"
				maxlength="20"
				placeholder="Complemento"
			/>
		</div>

		<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
			<AppMultiselect
				v-model="form.state"
				:options="stateOptions"
				field-label="Estado (*)"
				label="label"
				value-prop="value"
				:searchable="true"
				:close-on-select="true"
				:can-clear="true"
				:append-to-body="true"
				placeholder="Selecione o estado"
			/>

			<AppMultiselect
				v-model="form.city"
				:options="cityOptions"
				field-label="Cidade (*)"
				label="label"
				value-prop="value"
				:searchable="true"
				:close-on-select="true"
				:can-clear="true"
				:append-to-body="true"
				placeholder="Selecione a cidade"
			/>
		</div>
	</form>
</template>
