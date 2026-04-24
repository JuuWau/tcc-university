<script setup lang="ts">
import type { OpenClinicScheduleRow } from '@/types/schedule/openClinicSchedules';
import { Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data?: OpenClinicScheduleRow;
        onEdit?: (row: OpenClinicScheduleRow) => void;
        onRemove?: (row: OpenClinicScheduleRow) => void;
    };
}>();

function edit() {
    if (!props.params.data) return;
    props.params.onEdit?.(props.params.data);
}

function remove() {
    if (!props.params.data) return;
    props.params.onRemove?.(props.params.data);
}
</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">
        <Pencil
            class="cursor-pointer text-blue-500 hover:text-blue-700"
            :size="18"
            title="Editar agenda"
            @click="edit"
        />
        <Trash2
            class="cursor-pointer text-red-500 hover:text-red-700"
            :size="18"
            title="Excluir agenda"
            @click="remove"
        />
    </div>
</template>
