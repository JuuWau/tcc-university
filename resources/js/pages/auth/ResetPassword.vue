<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { Check, CircleAlert, Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
const password = ref('');
const passwordConfirmation = ref('');

const rules = computed(() => ({
    length: password.value.length >= 8,
    uppercase: /[A-Z]/.test(password.value),
    number: /\d/.test(password.value),
    special: /[^A-Za-z0-9]/.test(password.value),
}));
</script>

<template>
    <AuthLayout>
        <Head title="Redefinir senha" />

        <div class="flex min-h-[70vh] items-center justify-center">
            <div
                class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-8 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-6 text-center">
                    <img
                        src="/favicon.png"
                        alt="Logo"
                        class="mx-auto mb-3 h-10"
                    />

                    <h1
                        class="text-2xl font-bold text-zinc-800 dark:text-zinc-100"
                    >
                        Redefinir senha
                    </h1>

                    <p class="mt-1 text-sm text-zinc-500">
                        Informe sua nova senha para recuperar o acesso à sua
                        conta
                    </p>
                </div>

                <Form
                    :action="update().url"
                    method="post"
                    :transform="(data) => ({
                        ...data,
                        token: props.token,
                        email: inputEmail,
                    })"
                    :reset-on-success="[
                        'password',
                        'password_confirmation',
                    ]"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <BaseInput
                        id="email"
                        name="email"
                        type="email"
                        label="E-mail"
                        v-model="inputEmail"
                        autocomplete="email"
                        readonly
                        :error="errors.email"
                    />

                    <BaseInput
                        id="password"
                        name="password"
                        type="password"
                        label="Nova senha"
                        v-model="password"
                        placeholder="Digite sua nova senha"
                        autocomplete="new-password"
                        required
                        autofocus
                        :error="errors.password"
                    />

                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 p-3"
                    >
                        <p
                            class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-700"
                        >
                            <CircleAlert class="h-4 w-4" />
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

                    <BaseInput
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        label="Confirmar nova senha"
                        v-model="passwordConfirmation"
                        placeholder="Digite sua nova senha novamente"
                        autocomplete="new-password"
                        required
                        :error="errors.password_confirmation"
                    />

                    <Button
                        type="submit"
                        class="mt-2 h-11 w-full cursor-pointer rounded-lg bg-sky-600 text-base font-semibold transition-all hover:scale-[1.02] hover:bg-sky-700"
                        :disabled="processing"
                    >
                        <Spinner
                            v-if="processing"
                            class="mr-2"
                        />

                        <Save
                            v-else
                            class="mr-2 h-4 w-4"
                        />

                        {{ processing
                            ? 'Redefinindo senha...'
                            : 'Redefinir senha'
                        }}
                    </Button>
                </Form>

                <div
                    class="mt-6 text-center text-sm text-muted-foreground"
                >
                    <span>Lembrou sua senha? </span>

                    <TextLink
                        href="/login"
                        class="text-primary hover:underline"
                    >
                        Entrar
                    </TextLink>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>