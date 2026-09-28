<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const userName = computed(() => page.props.auth.user?.person?.name);
</script>

<template>
    <div class="flex items-center gap-3">
        <AppLogoIcon class="size-10 shrink-0 object-contain" />

        <div class="min-w-0">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-sky-600">
                Acadent
            </h1>

            <p class="truncate text-xs leading-tight text-slate-400">
                Olá, {{ userName }}
            </p>
        </div>
    </div>
</template>