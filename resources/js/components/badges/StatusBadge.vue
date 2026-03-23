<script setup lang="ts">
const props = defineProps<{
    params: {
        value: string;
    };
}>();

const status = props.params.value;

const classes: Record<string, string> = {
    Ativo: 'bg-green-200 text-green-800',
    Inativo: 'bg-gray-300 text-gray-500',
    Tratamento: 'bg-blue-100 text-blue-800',
    'Pausa no Tratamento': 'bg-amber-100 text-amber-800',
    Abandono: 'bg-red-100 text-red-800',
    Concluído: 'bg-emerald-100 text-emerald-800',
    Transferência: 'bg-violet-100 text-violet-800',
    Pendente: 'bg-yellow-100 text-yellow-800',
    Excluído: 'bg-gray-200 text-gray-600',
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
