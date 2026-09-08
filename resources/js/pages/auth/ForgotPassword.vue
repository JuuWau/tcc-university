<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Mail } from 'lucide-vue-next';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const emailSent = ref(false);
</script>

<template>
    <AuthLayout>
        <Head title="Esqueci minha senha" />

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
                        Esqueci minha senha
                    </h1>

                    <p class="mt-1 text-sm text-zinc-500">
                        Informe seu e-mail para receber um link de recuperação
                    </p>
                </div>

                <div
                    v-if="props.status || emailSent"
                    class="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-center text-sm font-medium text-green-700"
                >
                    Enviamos o link de redefinição de senha para o seu e-mail.
                </div>

                <Form
                    action="/forgot-password"
                    method="post"
                    @success="emailSent = true"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <BaseInput
                        id="email"
                        name="email"
                        type="email"
                        label="E-mail"
                        placeholder="seu@email.com"
                        autocomplete="email"
                        required
                        :error="errors.email"
                    />

                    <Button
                        type="submit"
                        class="h-11 w-full cursor-pointer rounded-lg bg-sky-600 text-base font-semibold transition-all hover:scale-[1.02] hover:bg-sky-700"
                        :disabled="processing"
                    >
                        <Spinner
                            v-if="processing"
                            class="mr-2"
                        />

                        <Mail
                            v-else
                            class="mr-2 h-4 w-4"
                        />

                        {{ processing
                            ? 'Enviando link...'
                            : 'Enviar link de recuperação'
                        }}
                    </Button>
                </Form>

                <div
                    class="mt-6 text-center text-sm text-muted-foreground"
                >
                    <span>Ou, voltar para </span>

                    <TextLink
                        href="/login"
                        class="text-primary hover:underline"
                    >
                        entrar
                    </TextLink>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>