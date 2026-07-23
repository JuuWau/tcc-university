<script setup lang="ts">
import { inject } from 'vue';
import { PatientClinicsKey } from '@/keys/patients/patientClinicsKeys';
import { PATIENT_CLINIC_STATUS, type PatientClinicStatus, } from '@/types/patient/patientClinics';

const ctx = inject(PatientClinicsKey);

const statusOptions: {
    value: PatientClinicStatus;
    label: string;
}[] = [
    {
        value: PATIENT_CLINIC_STATUS.WAITING,
        label: 'Lista de Espera',
    },
    {
        value: PATIENT_CLINIC_STATUS.ENROLLED,
        label: 'Inscritas',
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