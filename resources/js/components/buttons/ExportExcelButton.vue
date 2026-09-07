<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
        disabled?: boolean;
}>();

const emit = defineEmits<{
        click: [];
}>();
</script>

<template>
        <Button
                type="button"
                :disabled="disabled"
                class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                @click="emit('click')"
        >
                <Download class="h-4 w-4" />
                        Exportar Excel
        </Button>
</template>
