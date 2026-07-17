<script setup lang="ts">
import { Check, X, CalendarPlus } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data?: {
            id: number;
            status: string;
        };

        onConfirm?: (row: any) => void;
        onCancel?: (row: any) => void;
        onSchedule?: (row: any) => void;
    };
}>();

function confirm() {
    if (!props.params.data) return;

    props.params.onConfirm?.(props.params.data);
}

function cancel() {
    if (!props.params.data) return;

    props.params.onCancel?.(props.params.data);
}

function schedule() {
    if (!props.params.data) return;

    props.params.onSchedule?.(props.params.data);
}
</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">
        <template v-if="props.params.data?.status === 'scheduled'">
            <Check
                class="cursor-pointer text-green-600 hover:text-green-800"
                :size="18"
                title="Confirmar agendamento"
                @click="confirm"
            />

            <X
                class="cursor-pointer text-red-500 hover:text-red-700"
                :size="18"
                title="Cancelar agendamento"
                @click="cancel"
            />
        </template>

        <template v-else-if="props.params.data?.status === 'confirmed'">
            <X
                class="cursor-pointer text-red-500 hover:text-red-700"
                :size="18"
                title="Cancelar agendamento"
                @click="cancel"
            />
        </template>

        <template v-else-if="props.params.data?.status === 'canceled'">
            <CalendarPlus
                class="cursor-pointer text-sky-600 hover:text-sky-800"
                :size="18"
                title="Agendar"
                @click="schedule"
            />
        </template>
    </div>
</template>