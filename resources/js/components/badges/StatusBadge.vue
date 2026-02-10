<script setup lang="ts">
const props = defineProps<{
    params: {
        value: string;
    };
}>();

const status = props.params.value;

const classes: Record<string, string> = {
    Ativo: 'bg-green-100 text-green-800',
    Pendente: 'bg-yellow-100 text-yellow-800',
    Inativo: 'bg-gray-300 text-gray-500',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
        :class="classes[status] ?? 'bg-gray-100 text-gray-800'"
    >
        {{ status }}
    </span>
</template>
