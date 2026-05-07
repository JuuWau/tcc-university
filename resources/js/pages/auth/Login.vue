<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase>
        <Head title="Login" />

        <div class="flex min-h-[70vh] items-center justify-center">
            <div
                class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-8 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-6 text-center">
                    <img src="/favicon.png" class="mx-auto mb-3 h-10" />

                    <h1
                        class="text-2xl font-bold text-zinc-800 dark:text-zinc-100"
                    >
                        Bem-vindo de volta
                    </h1>
                    <p class="mt-1 text-sm text-zinc-500">
                        Entre com seu email e senha para continuar
                    </p>
                </div>

                <div
                    v-if="status"
                    class="mb-4 text-center text-sm font-medium text-green-600"
                >
                    {{ status }}
                </div>

                <Form
                    :action="store().url"
                    method="post"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="seu@email.com"
                            class="h-11 rounded-lg"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Senha</Label>

                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="h-11 rounded-lg"
                        />

                        <div class="flex justify-end">
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-xs text-primary hover:underline"
                            >
                                Esqueci minha senha
                            </TextLink>
                        </div>

                        <InputError :message="errors.password" />
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 h-11 w-full rounded-lg bg-sky-600 text-base font-semibold transition-all hover:scale-[1.02] hover:bg-sky-700"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        Entrar
                    </Button>
                </Form>
            </div>
        </div>
    </AuthBase>
</template>
