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
        class="cursor-pointer rounded bg-slate-200 px-4 py-1.5 hover:bg-slate-300"
        @click="emit('click')"
    >
        {{ label ?? 'Cancelar' }}
    </button>
</template>
