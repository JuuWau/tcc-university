<script setup lang="ts">
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import type { Procedure } from '@/types/procedure';

defineProps<{
    procedure: Procedure;
}>();

const emit = defineEmits<{
    edit: [procedure: Procedure];
    delete: [procedure: Procedure];
}>();
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-gray-500">Procedimento</p>
                <p class="mt-1 truncate text-sm font-medium text-gray-900">
                    {{ procedure.name }}
                </p>
                <p class="mt-1 text-sm text-gray-600">
                    {{ procedure.specialty?.name ?? 'Sem especialidade' }}
                </p>
            </div>

            <ActionsButtons
                :params="{
                    data: procedure,
                    onEdit: (selectedProcedure) =>
                        emit('edit', selectedProcedure),
                    onDelete: (selectedProcedure) =>
                        emit('delete', selectedProcedure),
                }"
            />
        </div>
    </div>
</template>
