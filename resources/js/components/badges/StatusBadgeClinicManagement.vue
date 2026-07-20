<script setup lang="ts">
const props = defineProps<{
    params: {
        value: string;
    };
}>();

const status = props.params.value;

const classes: Record<string, string> = {
    Inscrito: 'bg-green-200 text-green-800',
    'Lista de Espera': 'bg-yellow-200 text-yellow-800',
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