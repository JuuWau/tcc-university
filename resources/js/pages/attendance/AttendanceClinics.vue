<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceClinicCard from './components/AttendanceClinicCard.vue';
import { router } from '@inertiajs/vue3';
import { provide, ref, watch } from 'vue';

import {
    AttendanceManagementKey,
} from '@/keys/attendance/attendanceManagementKeys';

import type { AttendanceClinic } from '@/types/attendance/attendance';

const props = defineProps<{
    clinics: {
        data: AttendanceClinic[];
        }
}>();

const clinicsRef = ref<AttendanceClinic[]>([
    ...(props.clinics?.data ?? []),
]);

watch(
    () => props.clinics,
    (next) => {
        clinicsRef.value = [...(next?.data ?? [])];
    },
    { deep: true },
);

function goManageClinic(clinicId: number) {
    router.visit(`/attendance/clinics/${clinicId}`, {
        preserveScroll: true,
    });
}

provide(AttendanceManagementKey, {
    clinics: clinicsRef,
    goManageClinic,
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-10 w-full max-w-6xl px-4">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="mb-5 border-b border-gray-200 pb-4">
                    <h1
                        class="text-xl font-semibold tracking-tight text-gray-900"
                    >
                        Controle de Comparecimento
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Selecione uma clínica para gerenciar o comparecimento
                        dos alunos.
                    </p>
                </div>

                <div
                    v-if="!clinicsRef.length"
                    class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
                >
                    Nenhuma clínica disponível para gerenciamento de comparecimento.
                </div>

                <div
                    v-else
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <AttendanceClinicCard
                        v-for="clinic in clinicsRef"
                        :key="clinic.clinic_id"
                        :clinic="clinic"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>