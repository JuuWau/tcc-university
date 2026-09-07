<script setup lang="ts">
import ClinicTableActionsButtons from '@/components/buttons/ClinicTableActionsButtons.vue';
import type { Clinic } from '@/types/clinic/clinic';

defineProps<{
    clinic: Clinic;
    canDelete: boolean;
    canDeactivate: boolean;
}>();

const emit = defineEmits<{
    edit: [clinic: Clinic];
    deactivate: [clinic: Clinic];
    activate: [clinic: Clinic];
    delete: [clinic: Clinic];
}>();
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-gray-500">Clínica</p>
                <p class="mt-1 truncate text-sm font-medium text-gray-900">
                    {{ clinic.name }}
                </p>
                <span
                    class="mt-2 inline-flex rounded px-2 py-1 text-xs font-medium"
                    :class="
                        clinic.active
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-gray-100 text-gray-600'
                    "
                >
                    {{ clinic.active ? 'Ativa' : 'Inativa' }}
                </span>
            </div>

            <ClinicTableActionsButtons
                :params="{
                    data: clinic,
                    canDelete,
                    canDeactivate,
                    onEdit: (selectedClinic) =>
                        emit('edit', selectedClinic),
                    onDeactivate: (selectedClinic) =>
                        emit('deactivate', selectedClinic),
                    onActivate: (selectedClinic) =>
                        emit('activate', selectedClinic),
                    onDelete: (selectedClinic) =>
                        emit('delete', selectedClinic),
                }"
            />
        </div>

        <div class="mt-3 border-t border-gray-100 pt-3">
            <p class="mb-2 text-xs text-gray-500">Especialidades</p>
            <div
                v-if="clinic.specialties?.length"
                class="flex flex-wrap gap-1"
            >
                <span
                    v-for="specialty in clinic.specialties"
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
