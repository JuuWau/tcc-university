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
    scheduled: {
        label: 'Agendado',
        classes: 'bg-sky-200 text-sky-900',
    },

    confirmed: {
        label: 'Confirmado',
        classes: 'bg-green-200 text-green-900',
    },

    completed: {
        label: 'Concluído',
        classes: 'bg-teal-300 text-teal-900',
    },

    canceled: {
        label: 'Cancelado',
        classes: 'bg-red-200 text-red-900',
    },

    no_show: {
        label: 'Não compareceu',
        classes: 'bg-slate-300 text-slate-900',
    },

    rescheduled: {
        label: 'Remarcado',
        classes: 'bg-amber-300 text-amber-900',
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