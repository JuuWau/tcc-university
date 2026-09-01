<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { initialPage } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bem-vindo',
        href: initialPage(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Bem-vindo" />

        <div class="flex min-h-[90vh] items-center justify-center">
            <div
                class="w-full max-w-xl rounded-2xl border border-zinc-200 bg-white p-10 text-center shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mb-6">
                    <img src="/favicon.png" class="mx-auto mb-4 h-12" />
                </div>

                <h1
                    class="mb-2 text-3xl font-bold text-zinc-800 dark:text-zinc-100"
                >
                    Bem-vindo ao Acadent
                </h1>

                <p class="mb-8 text-zinc-500">
                    Seu acesso foi realizado com sucesso.
                </p>

                <div class="mt-8 text-sm text-zinc-400">
                    Se precisar de ajuda, entre em contato com o suporte.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
