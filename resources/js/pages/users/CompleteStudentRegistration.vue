<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { cpfSchema, studentCompleteSchema } from '@/schemas/accessComplete.schema';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const loading = ref(false);
const passwordConfirmation = ref('');

const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);

const viaCep = ViaCep();

const page = usePage<{ props: { email: string; token: string } }>();

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
    name: '' as string | null,
    email: page.props.email || null,
    phone: '' as string | null,
    cpf: '' as string | null,
    birth_date: '' as string | null,
    cep: '' as string | null,
    street: '' as string | null,
    neighborhood: '' as string | null,
    number: '' as string | null,
    complement: '' as string | null,
    city: '' as string | null,
    state: '' as string | null,
    password: '' as string | null,
});

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
    passwordConfirmation: '',
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
const complementInput = ref<{ focus: () => void } | null>(null);
const stateInput = ref<{ focus: () => void } | null>(null);
const cityInput = ref<{ focus: () => void } | null>(null);
const passwordInput = ref<{ focus: () => void } | null>(null);
const passwordConfirmationInput = ref<{ focus: () => void } | null>(null);

onMounted(async () => {
    states.value = await IbgeService.getUfData();
});

const rules = computed(() => ({
    length: (form.password?.length ?? 0) >= 8,
    uppercase: /[A-Z]/.test(form.password ?? ''),
    number: /\d/.test(form.password ?? ''),
    special: /[^A-Za-z0-9]/.test(form.password ?? ''),
}));

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
    errors.passwordConfirmation = '';
}

function focusFirstError(field: string) {
    switch (field) {
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
        case 'complement':
            complementInput.value?.focus();
            break;
        case 'state':
            stateInput.value?.focus();
            break;
        case 'city':
            cityInput.value?.focus();
            break;
        case 'password':
            passwordInput.value?.focus();
            break;
        case 'passwordConfirmation':
            passwordConfirmationInput.value?.focus();
            break;
    }
}

watch(
    () => form.cep,
    async (newCep) => {
        const cepClean = newCep?.replace(/\D/g, '');

        if (cepClean && cepClean.length === 8) {
            const data = await viaCep.getCepData(cepClean);

            if (!data) {
                return;
            }

            form.street = data.logradouro || '';
            form.neighborhood = data.bairro || '';
            form.city = data.localidade || '';
            form.state = data.uf || '';

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
            form.city = null;
            return;
        }

        cities.value = await IbgeService.getCityData(newState);

        if (!cities.value.find((city) => city.nome === form.city)) {
            form.city = null;
        }
    },
);

function validateCpf() {
    if (!form.cpf) {
        errors.cpf = '';
        return;
    }

    const result = cpfSchema.safeParse(form.cpf);

    if (!result.success) {
        errors.cpf = result.error.issues[0].message;
        return;
    }

    errors.cpf = '';
}

async function submit() {
    if (loading.value) {
        return;
    }

    clearErrors();

    const result = studentCompleteSchema.safeParse({
        ...form,
        passwordConfirmation: passwordConfirmation.value,
    });

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0] as keyof typeof errors;

            if (field in errors) {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0]?.path[0];

        if (firstError) {
            focusFirstError(firstError as string);
        }

        return;
    }

    try {
        loading.value = true;

        await axios.patch(`/invite/${page.props.token}`, {
            ...form,
        });

        toast.success('Cadastro concluído com sucesso!');

        router.visit('/login');
    } catch (err: any) {
        toast.error(
            err.response?.data?.message ||
                'Erro ao enviar cadastro',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-8"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow"
        >
            <div
                class="shrink-0 border-b border-gray-200 bg-white px-6 py-5 text-center"
            >
                <h1 class="text-xl font-bold text-gray-900">
                    Complete seu cadastro
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Defina sua senha para acessar a plataforma.
                </p>
            </div>

            <form
                class="min-h-0 flex-1 overflow-y-auto px-6"
                @submit.prevent="submit"
            >
                <div class="space-y-5 py-5">
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                    >
                        <BaseInput
                            ref="nameInput"
                            v-model="form.name"
                            label="Nome"
                            type="text"
                            maxlength="255"
                            placeholder="Nome completo"
                            required
                            :error="errors.name"
                        />

                        <BaseInput
                            ref="emailInput"
                            v-model="form.email"
                            label="Email"
                            type="email"
                            disabled
                            placeholder="email@exemplo.com"
                            required
                            :error="errors.email"
                        />

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
                            @blur="validateCpf"
                        />

                        <BaseInput
                            ref="birthDateInput"
                            v-model="form.birth_date"
                            label="Data de nascimento"
                            type="date"
                            required
                            :error="errors.birth_date"
                        />
                    </div>

                    <div class="border-t border-gray-200 pt-5">
                        <h2
                            class="mb-4 text-sm font-semibold text-gray-800"
                        >
                            Endereço
                        </h2>

                        <div
                            class="grid grid-cols-1 gap-4 sm:grid-cols-3"
                        >
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

                            <div class="sm:col-span-2">
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

                        <div
                            class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3"
                        >
                            <BaseInput
                                ref="neighborhoodInput"
                                v-model="form.neighborhood"
                                label="Bairro"
                                type="text"
                                maxlength="100"
                                placeholder="Bairro"
                                required
                                :error="errors.neighborhood"
                            />

                            <BaseInput
                                ref="numberInput"
                                v-model="form.number"
                                label="Número"
                                type="text"
                                maxlength="10"
                                placeholder="Número"
                                required
                                :error="errors.number"
                            />

                            <BaseInput
                                ref="complementInput"
                                v-model="form.complement"
                                label="Complemento"
                                type="text"
                                maxlength="20"
                                placeholder="Complemento"
                                required
                                :error="errors.complement"
                            />
                        </div>

                        <div
                            class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2"
                        >
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

                    <div class="border-t border-gray-200 pt-5">
                        <h2
                            class="mb-4 text-sm font-semibold text-gray-800"
                        >
                            Senha de acesso
                        </h2>

                        <BaseInput
                            ref="passwordInput"
                            v-model="form.password"
                            label="Senha"
                            type="password"
                            maxlength="50"
                            placeholder="Digite sua senha"
                            required
                            :error="errors.password"
                        />

                        <div
                            class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3"
                        >
                            <p
                                class="mb-2 text-xs font-medium text-gray-700"
                            >
                                A senha deve conter:
                            </p>

                            <ul class="space-y-1 text-xs">
                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.length
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" />
                                    Mínimo de 8 caracteres
                                </li>

                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.uppercase
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" />
                                    Pelo menos 1 letra maiúscula
                                </li>

                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.number
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" />
                                    Pelo menos 1 número
                                </li>

                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.special
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" />
                                    Pelo menos 1 caractere especial
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <BaseInput
                            ref="passwordConfirmationInput"
                            v-model="passwordConfirmation"
                            label="Confirmar senha"
                            type="password"
                            maxlength="50"
                            placeholder="Confirme sua senha"
                            required
                            :error="errors.passwordConfirmation"
                        />
                    </div>
                </div>
            </form>

            <div
                class="shrink-0 border-t border-gray-200 bg-white px-6 py-4"
            >
                <button
                    type="submit"
                    :disabled="loading"
                    class="inline-flex h-9 w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                    @click="submit"
                >
                    <LoadingSpinner
                        v-if="loading"
                        class="h-4 w-4"
                    />

                    <Check
                        v-else
                        class="h-4 w-4"
                    />

                    <span>
                        {{ loading ? 'Enviando...' : 'Concluir cadastro' }}
                    </span>
                </button>
            </div>

            <div
                class="shrink-0 border-t border-gray-100 bg-gray-50 px-6 py-3 text-center text-xs text-gray-400"
            >
                © {{ new Date().getFullYear() }} Sua Universidade
            </div>
        </div>
    </div>
</template>