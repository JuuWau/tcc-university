<script setup lang="ts">
import BaseCheckbox from '@/components/checkbox/BaseCheckbox.vue';
import OpenClinicSchedulesEnrollmentActionsButtons from '@/components/buttons/OpenClinicSchedulesEnrollmentActionsButtons.vue';
import type { OpenClinicScheduleEnrollmentRow } from '@/types/schedule-enrollment/openClinicSchedulesEnrollment';
import { formatDateBr } from '@/src/utils/formatters';

defineProps<{
    scheduleSlot: OpenClinicScheduleEnrollmentRow;
    selected: boolean;
    selectable: boolean;
}>();

const emit = defineEmits<{
    select: [value: boolean];
    enroll: [slot: OpenClinicScheduleEnrollmentRow];
}>();
</script>

<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
        :class="selected ? 'border-sky-400 ring-1 ring-sky-400' : ''"
    >
        <div class="flex items-start gap-3">
            <div class="shrink-0 pt-0.5">
                <BaseCheckbox
                    :model-value="selected"
                    :disabled="!selectable"
                    @update:model-value="emit('select', $event)"
                />
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="break-words font-semibold text-gray-900">
                    {{ formatDateBr(scheduleSlot.date) }}
                </h3>

                <div class="mt-2 space-y-1 text-sm text-gray-500">
                    <p>
                        <span class="font-medium text-gray-700">Horário:</span>
                        {{ scheduleSlot.start_time.slice(0, 5) }} às
                        {{ scheduleSlot.end_time.slice(0, 5) }}
                    </p>
                    <p>
                        <span class="font-medium text-gray-700">Período:</span>
                        {{ scheduleSlot.period_label }}
                    </p>
                    <p>
                        <span class="font-medium text-gray-700">Responsável:</span>
                        {{ scheduleSlot.responsible_names?.join(', ') || '—' }}
                    </p>
                    <p>
                        <span class="font-medium text-gray-700">Vagas:</span>
                        {{ scheduleSlot.available_slots }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
            <span
                class="rounded-full px-2 py-1 text-xs font-medium"
                :class="scheduleSlot.is_enrolled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
                {{ scheduleSlot.is_enrolled ? 'Inscrito' : 'Não inscrito' }}
            </span>

            <OpenClinicSchedulesEnrollmentActionsButtons
                :params="{
                    data: scheduleSlot,
                    onEnroll: (selectedSlot) => emit('enroll', selectedSlot),
                }"
            />
        </div>
    </div>
</template>
