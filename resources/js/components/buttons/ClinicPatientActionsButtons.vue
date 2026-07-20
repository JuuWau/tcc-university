<script setup lang="ts">
import { FilePlus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data: any;
        action: 'enroll' | 'remove';
        onEnroll?: (patient: any) => void;
        onRemove?: (patient: any) => void;
    };
}>();

function executeAction() {
    if (props.params.action === 'enroll') {
        props.params.onEnroll?.(props.params.data);
    }

    if (props.params.action === 'remove') {
        props.params.onRemove?.(props.params.data);
    }
}
</script>

<template>
    <FilePlus
        v-if="params.action === 'enroll'"
        class="cursor-pointer text-green-600 hover:text-green-800"
        :size="18"
        title="Inscrever"
        @click="executeAction"
    />

    <Trash2
        v-if="params.action === 'remove'"
        class="cursor-pointer text-red-600 hover:text-red-800"
        :size="18"
        title="Remover inscrição"
        @click="executeAction"
    />
</template>