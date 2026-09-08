<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { cpfSchema, staffCompleteSchema } from '@/schemas/accessComplete.schema';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { CircleAlert } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const loading = ref(false);
const passwordConfirmation = ref('');
const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);
const viaCep = ViaCep();
const page = usePage();
const props = page.props as unknown as {
    email: string;
    token: string;
    name?: string;
};
const cpfError = ref<string | null>(null);

const stateOptions = computed(() =>
    states.value.map((s) => ({ label: s.nome, value: s.sigla })),
);

const cityOptions = computed(() =>
    cities.value.map((c) => ({ label: c.nome, value: c.nome })),
);

const form = reactive({
    name: props.name || '',
    email: props.email || '',
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

onMounted(async () => {
    states.value = await IbgeService.getUfData();
});

const rules = computed(() => ({
    length: (form.password?.length ?? 0) >= 8,
    uppercase: /[A-Z]/.test(form.password ?? ''),
    number: /\d/.test(form.password ?? ''),
    special: /[^A-Za-z0-9]/.test(form.password ?? ''),
}));

watch(
    () => form.cep,
    async (newCep) => {
        const cepClean = newCep?.replace(/\D/g, '');
        if (cepClean && cepClean.length === 8) {
            const data = await viaCep.getCepData(cepClean);
            if (!data) return;

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
        if (!cities.value.find((c) => c.nome === form.city)) {
            form.city = null;
        }
    },
);

function validateCpf() {
    if (!form.cpf) {
        cpfError.value = null;
        return;
    }

    const result = cpfSchema.safeParse(form.cpf);

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        form.cpf = null;
    }
}

async function submit() {
    if (loading.value) return;

    const result = staffCompleteSchema.safeParse({
        ...form,
        passwordConfirmation: passwordConfirmation.value,
    });

    if (!result.success) {
        const error = result.error.issues[0];
        toast.error(error.message);
        return;
    }

    try {
        loading.value = true;

        await axios.post(`/invite/${props.token}`, {
            name: form.name,
            phone: form.phone,
            cpf: form.cpf,
            birth_date: form.birth_date,
            cep: form.cep,
            street: form.street,
            neighborhood: form.neighborhood,
            number: form.number,
            complement: form.complement ?? '',
            city: form.city,
            state: form.state,
            password: form.password,
            password_confirmation: passwordConfirmation.value,
        });

        toast.success('Cadastro concluído com sucesso!');
        router.visit('/login');
    } catch (err: any) {
        const msg =
            (err.response?.data?.message ?? err.response?.data?.errors)
                ? Object.values(err.response?.data?.errors).flat().join(', ')
                : 'Erro ao enviar cadastro';
        toast.error(msg);
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
                    Preencha seus dados e defina sua senha para acessar a
                    plataforma.
                </p>
            </div>
            <form
                class="min-h-0 flex-1 overflow-y-auto px-6"
                @submit.prevent="submit"
            >
                <div class="space-y-5 py-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <BaseInput
                            v-model="form.name"
                            label="Nome (*)"
                            type="text"
                            maxlength="255"
                            placeholder="Seu nome completo"
                        />
                        <BaseInput
                            v-model="form.email"
                            label="Email (*)"
                            type="email"
                            disabled
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
                            @blur="validateCpf"
                        />
                    </div>
                    <BaseInput
                        v-model="form.birth_date"
                        label="Data de nascimento (*)"
                        type="date"
                    />
                    <div class="border-t border-gray-200 pt-5">
                        <h2 class="mb-4 text-sm font-semibold text-gray-800">
                            Endereço
                        </h2>
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
                                maxlength="100"
                                placeholder="Bairro"
                            />
                            <BaseInput
                                v-model="form.number"
                                label="Número (*)"
                                type="text"
                                maxlength="10"
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
                    <div class="border-t border-gray-200 pt-5">
                        <h2 class="mb-4 text-sm font-semibold text-gray-800">
                            Senha de acesso
                        </h2>
                        <BaseInput
                            v-model="form.password"
                            label="Senha (*)"
                            type="password"
                            maxlength="50"
                            placeholder="Digite sua senha"
                        />
                        <div
                            class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3"
                        >
                            <p
                                class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-700"
                            >
                                <CircleAlert class="h-4 w-4" /> A senha deve
                                conter:
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
                                    <Check class="h-3.5 w-3.5" /> Mínimo de 8
                                    caracteres
                                </li>
                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.uppercase
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" /> Pelo menos 1
                                    letra maiúscula
                                </li>
                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.number
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" /> Pelo menos 1
                                    número
                                </li>
                                <li
                                    class="flex items-center gap-2"
                                    :class="
                                        rules.special
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    <Check class="h-3.5 w-3.5" /> Pelo menos 1
                                    caractere especial
                                </li>
                            </ul>
                        </div>
                    </div>
                    <BaseInput
                        v-model="passwordConfirmation"
                        label="Confirmar senha (*)"
                        type="password"
                        maxlength="50"
                        placeholder="Confirme sua senha"
                    />
                    <p
                        v-if="
                            passwordConfirmation &&
                            form.password !== passwordConfirmation
                        "
                        class="text-xs text-red-600"
                    >
                        As senhas não coincidem.
                    </p>
                </div>
            </form>
            <div
                class="flex shrink-0 justify-end border-t border-gray-200 bg-white px-6 py-4"
            >
                <button
                    type="button"
                    :disabled="loading"
                    class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 focus:outline-none active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                    @click="submit"
                >
                    <LoadingSpinner v-if="loading" class="h-4 w-4" />
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
