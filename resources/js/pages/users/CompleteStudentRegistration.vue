<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
import {
    cpfSchema,
    studentCompleteSchema,
} from '@/schemas/accessComplete.schema';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

// const page = usePage();
// console.log(page);
// watch(
//     () => page.props,
//     (props) => {
//         console.log(props);
//     },
// );

const loading = ref(false);
const passwordConfirmation = ref('');
const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);
const viaCep = ViaCep();
const page = usePage<{ props: { email: string; token: string } }>();
const cpfError = ref<string | null>(null);

const stateOptions = computed(() =>
    states.value.map((s) => ({ label: s.nome, value: s.sigla })),
);

const cityOptions = computed(() =>
    cities.value.map((c) => ({ label: c.nome, value: c.nome })),
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
    if (loading?.value) return;

    const result = studentCompleteSchema.safeParse({
        ...form,
        passwordConfirmation: passwordConfirmation.value,
    });

    if (!result.success) {
        // Mostra o primeiro erro
        const error = result.error.issues[0];
        toast.error(error.message);
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
        toast.error(err.response?.data?.message || 'Erro ao enviar cadastro');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-3xl rounded-lg bg-white p-6 shadow">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-bold text-gray-800">
                    Complete seu cadastro
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Defina sua senha para acessar a plataforma
                </p>
            </div>

            <form class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Email -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Email (*)
                        </label>
                        <input
                            type="email"
                            v-model="form.email"
                            disabled
                            class="w-full cursor-not-allowed rounded border bg-gray-100 px-3 py-2 text-sm text-gray-600"
                        />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Telefone (*)
                        </label>
                        <input
                            v-model="form.phone"
                            v-mask="'(##) #####-####'"
                            type="tel"
                            placeholder="(99) 99999-9999"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Email -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            CPF (*)
                        </label>
                        <input
                            type="cpf"
                            v-model="form.cpf"
                            maxlength="14"
                            @blur="validateCpf"
                            v-mask="'###.###.###-##'"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Data de Nascimento (*)
                        </label>
                        <input
                            v-model="form.birth_date"
                            type="date"
                            maxlength="15"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-1">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            CEP (*)
                        </label>
                        <input
                            v-model="form.cep"
                            v-mask="'#####-###'"
                            type="text"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Endereço (*)
                        </label>
                        <input
                            v-model="form.street"
                            type="text"
                            maxlength="100"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Bairro (*)
                        </label>
                        <input
                            v-model="form.neighborhood"
                            type="text"
                            maxlength="100"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Número (*)
                        </label>
                        <input
                            v-model="form.number"
                            type="text"
                            maxlength="10"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Complemento (*)
                        </label>
                        <input
                            v-model="form.complement"
                            type="text"
                            maxlength="20"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Estado (*)
                        </label>
                        <AppMultiselect
                            v-model="form.state"
                            :options="stateOptions"
                            label="label"
                            value-prop="value"
                            :searchable="true"
                            :close-on-select="true"
                            :can-clear="true"
                            placeholder="Selecione a cidade"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Cidade (*)
                        </label>
                        <AppMultiselect
                            v-model="form.city"
                            :options="cityOptions"
                            label="label"
                            value-prop="value"
                            :searchable="true"
                            :close-on-select="true"
                            :can-clear="true"
                            placeholder="Selecione a cidade"
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Senha (*)
                    </label>

                    <input
                        v-model="form.password"
                        type="password"
                        maxlength="50"
                        class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        placeholder="Digite sua senha"
                    />

                    <!-- Regras -->
                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                        <li :class="rules.length ? 'text-green-600' : ''">
                            • Mínimo de 8 caracteres
                        </li>
                        <li :class="rules.uppercase ? 'text-green-600' : ''">
                            • Pelo menos 1 letra maiúscula
                        </li>
                        <li :class="rules.number ? 'text-green-600' : ''">
                            • Pelo menos 1 número
                        </li>
                        <li :class="rules.special ? 'text-green-600' : ''">
                            • Pelo menos 1 caractere especial
                        </li>
                    </ul>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Confirmar senha (*)
                    </label>

                    <input
                        v-model="passwordConfirmation"
                        type="password"
                        maxlength="50"
                        class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        placeholder="Confirme sua senha"
                    />

                    <p
                        v-if="
                            passwordConfirmation &&
                            form.password !== passwordConfirmation
                        "
                        class="mt-1 text-xs text-red-600"
                    >
                        As senhas não coincidem
                    </p>
                </div>

                <button
                    type="button"
                    @click="submit"
                    class="mt-4 w-full rounded bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700"
                >
                    Concluir cadastro
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-gray-400">
                © {{ new Date().getFullYear() }} Sua Universidade
            </div>
        </div>
    </div>
</template>
