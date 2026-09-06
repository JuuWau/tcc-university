<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';

defineProps<{
        loading?: boolean;
}>();

const emit = defineEmits<{
        cancel: [];
        save: [];
}>();

</script>

<template>
        <div class="flex shrink-0 justify-end gap-2 border-t border-gray-200 bg-white p-4">
                <CancelButton @click="emit('cancel')" />

                <SaveButton
                        :loading="loading"
                        @click.stop="emit('save')"
                />
        </div>
</template>