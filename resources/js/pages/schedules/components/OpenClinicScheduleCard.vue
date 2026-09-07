<script setup lang="ts">
import BaseCheckbox from '@/components/checkbox/BaseCheckbox.vue';
import type { OpenClinicScheduleRow } from '@/types/schedule/openClinicSchedules';
import { Pencil, Trash2, UserPlus } from 'lucide-vue-next';

const props = defineProps<{
    slot: OpenClinicScheduleRow;
    selected: boolean;
    selectable: boolean;
}>();

const emit = defineEmits<{
    select: [value: boolean];
    edit: [slot: OpenClinicScheduleRow];
    remove: [slot: OpenClinicScheduleRow];
    addStudents: [slot: OpenClinicScheduleRow];
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
                    {{ slot.date }}
                </h3>

                <div class="mt-2 space-y-1 text-sm text-gray-500">
                    <p>
                        <span class="font-medium text-gray-700">
                            Horário:
                        </span>
                        {{ slot.start_time }} - {{ slot.end_time }}
                    </p>

                    <p>
                        <span class="font-medium text-gray-700">
                            Clínica:
                        </span>
                        {{ slot.clinic_name }}
                    </p>

                    <p>
                        <span class="font-medium text-gray-700">
                            Vagas:
                        </span>
                        {{ slot.available_slots }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mt-4 flex items-center justify-end gap-4 border-t border-gray-100 pt-3"
        >
            <Pencil
                class="cursor-pointer text-blue-500 hover:text-blue-700"
                :size="20"
                title="Editar agenda"
                @click="emit('edit', props.slot)"
            />

            <Trash2
                class="cursor-pointer text-red-500 hover:text-red-700"
                :size="20"
                title="Excluir agenda"
                @click="emit('remove', props.slot)"
            />

            <UserPlus
                class="cursor-pointer text-green-500 hover:text-green-700"
                :size="20"
                title="Adicionar estudantes"
                @click="emit('addStudents', props.slot)"
            />
        </div>
    </div>
</template>
