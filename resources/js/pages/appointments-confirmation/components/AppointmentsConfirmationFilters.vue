<script setup lang="ts">
import { computed, inject } from 'vue';

import {
    AppointmentsConfirmationKey,
} from '@/keys/appointments-confirmation/appointmentsConfirmationKeys';
import Button from '@/components/ui/button/Button.vue';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { X } from 'lucide-vue-next';

const context = inject(AppointmentsConfirmationKey);

if (!context) {
    throw new Error(
        'AppointmentsConfirmationContext não encontrado.'
    );
}

const {
    filters,
    clinics,
    periods,
    searchAppointments,
    clearFilters,
} = context;

const clinicOptions = computed(() =>
    clinics.value.map((clinic) => ({
        label: clinic.label,
        value: clinic.id,
    }))
);

const periodOptions = computed(() =>
    periods.value.map((period) => ({
        label: period.label,
        value: period.id,
    }))
);
</script>
<template>
    <div
        class="mb-6 grid items-end gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:grid-cols-6"
    >
        <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Clínica
            </label>

            <AppMultiselect
                v-model="filters.clinic_id"
                :options="clinicOptions"
                label="label"
                value-prop="value"
                placeholder="Todas as clínicas"
                :searchable="true"
                :can-clear="true"
                :append-to-body="true"
                @update:modelValue="searchAppointments"
            />
        </div>

        <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Período
            </label>

            <AppMultiselect
                v-model="filters.period_id"
                :options="periodOptions"
                label="label"
                value-prop="value"
                placeholder="Todos os períodos"
                :searchable="true"
                :can-clear="true"
                :append-to-body="true"
                @update:modelValue="searchAppointments"
            />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Data
            </label>

            <input
                v-model="filters.date"
                type="date"
                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                @input="searchAppointments"
            />
        </div>

        <div class="flex h-full items-end justify-start">
            <Button
                variant="outline"
                class="flex w-full items-center gap-2 cursor-pointer"
                @click="clearFilters"
            >
                <X class="h-4 w-4" />
                Limpar
            </Button>
        </div>
    </div>
</template>