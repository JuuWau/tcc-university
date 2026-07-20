<script setup lang="ts">
import { inject } from 'vue';
import { ClinicManagementShowKey, } from '@/keys/clinics-management/clinicManagementShowKeys';
import { CLINIC_PATIENT_STATUS, type ClinicPatientStatus, } from '@/types/clinics-management/clinicManagement';

const ctx = inject(ClinicManagementShowKey);

const statusOptions: {
    value: ClinicPatientStatus;
    label: string;
}[] = [
    {
        value: CLINIC_PATIENT_STATUS.WAITING,
        label: 'Lista de Espera',
    },
    {
        value: CLINIC_PATIENT_STATUS.ENROLLED,
        label: 'Inscritos',
    },
];
</script>

<template>
    <div
        class="mb-4 flex flex-wrap gap-1 rounded-full bg-gray-100 p-1"
    >
        <button
            v-for="option in statusOptions"
            :key="option.value"
            type="button"
            class="cursor-pointer rounded-full px-3 py-1.5 text-sm font-medium transition-all"
            :class="
                ctx?.activeStatus.value === option.value
                    ? 'bg-white text-gray-900 shadow'
                    : 'text-gray-500 hover:text-gray-900'
            "
            @click="ctx?.setStatus(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>