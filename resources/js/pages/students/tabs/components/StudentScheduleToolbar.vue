<script setup lang="ts">
import Multiselect from '@vueform/multiselect';
import { computed, inject } from 'vue';
import { StudentScheduleContextKey, type StudentScheduleContext } from '@/keys/students/studentScheduleKeys';

const schedule = inject( StudentScheduleContextKey ) as StudentScheduleContext;

if (!schedule) {
    throw new Error(
        'StudentScheduleToolbar must be used inside StudentScheduleTab',
    );
}

const clinicOptions = computed(() => schedule.clinics.value);
const selectedClinicValue = computed({
    get: () => schedule.selectedClinic.value,
    set: (value) => {
        schedule.selectedClinic.value = value;
    }
});
</script>

<template>
    <div
        class="flex flex-col gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 md:flex-row md:items-end md:justify-between"
    >
        <div class="w-full max-w-xs">
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Clínica
            </label>

            <Multiselect
                v-model="selectedClinicValue"
                :options="clinicOptions"
                label="label"
                track-by="value"
                value-prop="value"
                :searchable="true"
                :close-on-select="true"
                :can-clear="false"
                placeholder="Selecione a clínica"
            />
        </div>

        <div class="flex items-center gap-2 self-end">
            <button
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm transition hover:bg-gray-100"
                @click="schedule.prevMonths"
            >
                ←
            </button>

            <div
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700"
            >
                Período
            </div>

            <button
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm transition hover:bg-gray-100"
                @click="schedule.nextMonths"
            >
                →
            </button>
        </div>
    </div>
</template>