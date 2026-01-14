<script setup lang="ts">
import LoadingSpinner from '@/components/ui/spinner/Spinner.vue';

defineProps<{ loading?: boolean }>();
</script>

<template>
    <button
        type="button"
        :disabled="loading"
        class="flex items-center gap-2 rounded bg-blue-400 px-4 py-2 text-white disabled:opacity-60"
    >
        <LoadingSpinner v-if="loading" class="h-4 w-4" />
        <span>{{ loading ? 'Salvando...' : 'Salvar' }}</span>
    </button>
</template>
