<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    params: {
        value: string;
    };
}>();

const statusMap: Record<
    string,
    {
        label: string;
        classes: string;
    }
> = {
    ativo: {
        label: 'Ativo',
        classes: 'bg-green-200 text-green-900',
    },

    inativo: {
        label: 'Inativo',
        classes: 'bg-slate-300 text-slate-900',
    },

    tratamento: {
        label: 'Tratamento',
        classes: 'bg-sky-200 text-sky-900',
    },

    pausa_tratamento: {
        label: 'Pausa no Tratamento',
        classes: 'bg-amber-200 text-amber-900',
    },

    abandono: {
        label: 'Abandono',
        classes: 'bg-red-200 text-red-900',
    },

    concluido: {
        label: 'Concluído',
        classes: 'bg-teal-300 text-teal-900',
    },

    transferencia: {
        label: 'Transferência',
        classes: 'bg-purple-200 text-purple-900',
    },
};

const badge = computed(() =>
    statusMap[props.params.value] ?? {
        label: props.params.value ?? '-',
        classes: 'bg-gray-100 text-gray-800',
    }
);
</script>

<template>
    <div class="flex h-full items-center justify-center">
        <span
            :class="[
                badge.classes,
                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            ]"
        >
            {{ badge.label }}
        </span>
    </div>
</template>