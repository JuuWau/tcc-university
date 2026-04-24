<script setup lang="ts">
const emit = defineEmits<{
    (e: 'click'): void;
}>();

defineProps<{
    label?: string;
}>();
</script>

<template>
    <button
        type="button"
        class="h-9 cursor-pointer rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-300"
        @click="emit('click')"
    >
        {{ label ?? 'Cancelar' }}
    </button>
</template>
