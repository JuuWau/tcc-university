<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { StudentTabContextKey } from '@/keys/students/studentKeys';
import { studentEditSchema } from '@/schemas/studentEdit.schema';
import type { Student } from '@/types/student/student';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const context = inject(StudentTabContextKey);
if (!context) {
    throw new Error('StudentsEditModal must be used inside StudentTab');
}

const student = computed(() => context.student.value);
const editModalOpen = context.editModalOpen;

const emit = defineEmits<{
    updated: [];
}>();

const loading = ref(false);
const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);
const viaCep = ViaCep();

const stateOptions = computed(() =>
    states.value.map((s) => ({ label: s.nome, value: s.sigla })),
);

const cityOptions = computed(() =>
    cities.value.map((c) => ({ label: c.nome, value: c.nome })),
);

const form = reactive({
    name: '' as string,
    email: '' as string,
    phone: '' as string,
    cpf: '' as string,
    birth_date: '' as string,
    cep: '' as string,
    street: '' as string,
    neighborhood: '' as string,
    number: '' as string,
    complement: '' as string | null,
    city: '' as string,
    state: '' as string,
    password: '' as string | null,
});

function formatDateForInput(dateStr: string | null | undefined): string {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return '';
    return d.toISOString().slice(0, 10);
}

function populateForm() {
    const s = student.value;
    const p = s.person;
    const u = s.user;
    const addr = p?.address;

    form.name = p?.name ?? '';
    form.email = u?.email ?? '';
    form.phone = p?.phone ?? '';
    form.cpf = p?.cpf ?? '';
    form.birth_date = formatDateForInput(p?.birth_date);
    form.cep = addr?.cep ?? '';
    form.street = addr?.street ?? '';
    form.neighborhood = addr?.neighborhood ?? '';
    form.number = addr?.number ?? '';
    form.complement = addr?.complement ?? '';
    form.city = addr?.city ?? '';
    form.state = addr?.state ?? '';
    form.password = '';
}

watch(
    () => editModalOpen.value,
    (isOpen) => {
        if (isOpen) {
            populateForm();
            void loadStates();
        }
    },
);

async function loadStates() {
    if (states.value.length) return;
    states.value = await IbgeService.getUfData();
}

watch(
    () => form.cep,
    async (newCep) => {
        const cepClean = newCep?.replace(/\D/g, '');
        if (cepClean && cepClean.length === 8) {
            const data = await viaCep.getCepData(cepClean);
            if (!data) return;
            form.street = data.logradouro ?? '';
            form.neighborhood = data.bairro ?? '';
            form.city = data.localidade ?? '';
            form.state = data.uf ?? '';
            if (data.uf) {
                cities.value = await IbgeService.getCityData(data.uf);
            }
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
        if (!cities.value.some((c) => c.nome === form.city)) {
            form.city = '';
        }
    },
);

function close() {
    editModalOpen.value = false;
}

async function submit() {
    if (loading.value) return;

    const payload: Record<string, unknown> = {
        name: form.name,
        email: form.email,
        phone: form.phone,
        cpf: form.cpf,
        birth_date: form.birth_date,
        cep: form.cep,
        street: form.street,
        neighborhood: form.neighborhood,
        number: form.number,
        complement: form.complement || null,
        city: form.city,
        state: form.state,
    };
    if (form.password && form.password.trim()) {
        payload.password = form.password;
    }

    const result = studentEditSchema.safeParse(payload);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const { data } = await axios.patch<{
            message: string;
            student: Student;
        }>(`/students/${student.value.id}`, payload);
        toast.success(data.message ?? 'Dados atualizados com sucesso');
        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err && typeof err === 'object' && 'response' in err
                ? (err as { response?: { data?: { message?: string } } })
                      .response?.data?.message
                : null;
        toast.error(message ?? 'Erro ao atualizar dados do aluno');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="editModalOpen"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar dados do aluno"
				subtitle="Atualize os dados pessoais e de contato do aluno."
			/>

			<form
				class="min-h-0 flex-1 overflow-y-auto px-6"
				@submit.prevent="submit"
			>
				<div class="space-y-4 py-5">
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
							label="E-mail (*)"
							type="email"
							placeholder="email@exemplo.com"
						/>
					</div>

					<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
						<BaseInput
							v-model="form.phone"
							label="Telefone (*)"
							type="tel"
							v-mask="'(##) #####-####'"
							placeholder="(99) 99999-9999"
						/>

						<BaseInput
							v-model="form.cpf"
							label="CPF (*)"
							type="text"
							maxlength="14"
							v-mask="'###.###.###-##'"
							placeholder="000.000.000-00"
						/>
					</div>

					<BaseInput
						v-model="form.birth_date"
						label="Data de nascimento (*)"
						type="date"
					/>

					<div class="border-t border-gray-200 pt-4">
						<h3 class="mb-3 text-sm font-semibold text-gray-700">
							Endereço
						</h3>

						<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
					</div>
				</div>
			</form>

			<FormFooter
				:loading="loading"
				action="save"
				action-label="Salvar"
				@cancel="close"
				@save="submit"
			/>
		</div>
	</div>
</template>