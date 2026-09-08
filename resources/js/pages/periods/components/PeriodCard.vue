<script setup lang="ts">
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import type { Period } from '@/types/period';

defineProps<{
    period: Period;
}>();

const emit = defineEmits<{
    edit: [period: Period];
    delete: [period: Period];
}>();
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <p class="text-xs text-gray-500">Período</p>

                <p class="text-sm font-medium text-gray-900">
                    {{ period.academic_year }}º ano ·
                    {{ period.semester }}º semestre
                </p>

                <p class="text-sm text-gray-600">
                    Ano-calendário: {{ period.calendar_year }}
                </p>
            </div>

            <ActionsButtons
                :params="{
                    data: period,
                    onEdit: (selectedPeriod) => emit('edit', selectedPeriod),
                    onDelete: (selectedPeriod) =>
                        emit('delete', selectedPeriod),
                }"
            />
        </div>

        <div class="mt-3 border-t border-gray-100 pt-3">
            <p class="mb-2 text-xs text-gray-500">Especialidades</p>

            <div
                v-if="period.specialties?.length"
                class="flex flex-wrap gap-1"
            >
                <span
                    v-for="specialty in period.specialties"
                    :key="specialty.id"
                    class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700"
                >
                    {{ specialty.name }}
                </span>
            </div>

            <p v-else class="text-sm text-gray-500">
                Nenhuma especialidade vinculada.
            </p>
        </div>
    </div>
</template>
