<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data: { id: number };
        onEdit?: (item: any) => void;
        onDelete?: (item: any) => void;
    };
}>();

function edit() {
    props.params.onEdit?.(props.params.data);
}

function remove() {
    props.params.onDelete?.(props.params.data);
}
</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">
        <Pencil
            class="cursor-pointer text-blue-500 hover:text-blue-700"
            :size="18"
            @click="edit"
        />

        <Trash2
            class="cursor-pointer text-red-500 hover:text-red-700"
            :size="18"
            @click="remove"
        />
    </div>
</template>