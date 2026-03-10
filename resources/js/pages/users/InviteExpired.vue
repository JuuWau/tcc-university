<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 text-center shadow">
            <h1 class="text-xl font-bold text-gray-800">
                Link inválido ou expirado
            </h1>
            <p class="mt-2 text-sm text-gray-500">
                Este link de convite já foi utilizado ou expirou. Solicite um novo convite ao administrador.
            </p>
            <Link
                href="/login"
                class="mt-6 inline-block rounded bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700"
            >
                Ir para o login
            </Link>
        </div>
    </div>
</template>
