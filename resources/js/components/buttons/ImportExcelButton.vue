<script setup lang="ts">
import Button from '../ui/button/Button.vue';
import { Upload } from 'lucide-vue-next';

defineEmits<{
    click: [];
}>();
</script>

<template>
    <Button
        type="button"
        class="border border-green-500 bg-green-500 text-white hover:bg-green-600 cursor-pointer"
        @click="$emit('click')"
    >
        <Upload class="mr-2 h-4 w-4" />

        Importar Excel
    </Button>
</template>