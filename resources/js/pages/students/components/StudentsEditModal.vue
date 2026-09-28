<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
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

const errors = reactive({
    name: '',
    email: '',
    phone: '',
    cpf: '',
    birth_date: '',
    cep: '',
    street: '',
    neighborhood: '',
    number: '',
    complement: '',
    city: '',
    state: '',
    password: '',
});

const nameInput = ref<{ focus: () => void } | null>(null);
const emailInput = ref<{ focus: () => void } | null>(null);
const phoneInput = ref<{ focus: () => void } | null>(null);
const cpfInput = ref<{ focus: () => void } | null>(null);
const birthDateInput = ref<{ focus: () => void } | null>(null);
const cepInput = ref<{ focus: () => void } | null>(null);
const streetInput = ref<{ focus: () => void } | null>(null);
const neighborhoodInput = ref<{ focus: () => void } | null>(null);
const numberInput = ref<{ focus: () => void } | null>(null);
const cityInput = ref<{ focus: () => void } | null>(null);
const stateInput = ref<{ focus: () => void } | null>(null);

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

function clearErrors() {
    errors.name = '';
    errors.email = '';
    errors.phone = '';
    errors.cpf = '';
    errors.birth_date = '';
    errors.cep = '';
    errors.street = '';
    errors.neighborhood = '';
    errors.number = '';
    errors.complement = '';
    errors.city = '';
    errors.state = '';
    errors.password = '';
}

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
    clearErrors();
}

async function submit() {
    if (loading.value) return;

    clearErrors();

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
        result.error.issues.forEach((issue) => {
            const field = issue.path[0];

            if (field in errors) {
                errors[field as keyof typeof errors] = issue.message;
            }
        });

        const firstError = result.error.issues[0];

        switch (firstError.path[0]) {
            case 'name':
                nameInput.value?.focus();
                break;
            case 'email':
                emailInput.value?.focus();
                break;
            case 'phone':
                phoneInput.value?.focus();
                break;
            case 'cpf':
                cpfInput.value?.focus();
                break;
            case 'birth_date':
                birthDateInput.value?.focus();
                break;
            case 'cep':
                cepInput.value?.focus();
                break;
            case 'street':
                streetInput.value?.focus();
                break;
            case 'neighborhood':
                neighborhoodInput.value?.focus();
                break;
            case 'number':
                numberInput.value?.focus();
                break;
            case 'state':
                stateInput.value?.focus();
                break;
            case 'city':
                cityInput.value?.focus();
                break;
        }

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
                            ref="nameInput"
                            v-model="form.name"
                            label="Nome completo"
                            type="text"
                            maxlength="255"
                            placeholder="Nome completo"
                            required
                            :error="errors.name"
                        />

                        <BaseInput
                            ref="emailInput"
                            v-model="form.email"
                            label="E-mail"
                            type="email"
                            placeholder="email@exemplo.com"
                            required
                            :error="errors.email"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <BaseInput
                            ref="phoneInput"
                            v-model="form.phone"
                            label="Telefone"
                            type="tel"
                            v-mask="'(##) #####-####'"
                            placeholder="(99) 99999-9999"
                            required
                            :error="errors.phone"
                        />

                        <BaseInput
                            ref="cpfInput"
                            v-model="form.cpf"
                            label="CPF"
                            type="text"
                            maxlength="14"
                            v-mask="'###.###.###-##'"
                            placeholder="000.000.000-00"
                            required
                            :error="errors.cpf"
                        />
                    </div>

                    <BaseInput
                        ref="birthDateInput"
                        v-model="form.birth_date"
                        label="Data de nascimento"
                        type="date"
                        required
                        :error="errors.birth_date"
                    />

                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="mb-3 text-sm font-semibold text-gray-700">
                            Endereço
                        </h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <BaseInput
                                ref="cepInput"
                                v-model="form.cep"
                                label="CEP"
                                type="text"
                                maxlength="9"
                                v-mask="'#####-###'"
                                placeholder="00000-000"
                                required
                                :error="errors.cep"
                            />

                            <div class="md:col-span-2">
                                <BaseInput
				    ref="streetInput"
                                    v-model="form.street"
                                    label="Endereço"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Logradouro"
                                    required
				    :error="errors.street"
                                />
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <BaseInput
			        ref="neighborhoodInput"
                                v-model="form.neighborhood"
                                label="Bairro"
                                type="text"
                                maxlength="50"
                                placeholder="Bairro"
                                required
				:error="errors.neighborhood"
                            />

                            <BaseInput
			        ref="numberInput"
                                v-model="form.number"
                                label="Número"
                                type="text"
                                maxlength="5"
                                placeholder="Número"
                                required
				:error="errors.number"
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
				ref="stateInput"
                                v-model="form.state"
                                :options="stateOptions"
                                field-label="Estado"
                                label="label"
                                value-prop="value"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                :append-to-body="true"
                                placeholder="Selecione o estado"
                                required
				:error="errors.state"
                            />

                            <AppMultiselect
			        ref="cityInput"
                                v-model="form.city"
                                :options="cityOptions"
                                field-label="Cidade"
                                label="label"
                                value-prop="value"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                :append-to-body="true"
                                placeholder="Selecione a cidade"
                                required
				:error="errors.city"
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
