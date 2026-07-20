<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { provide, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ClinicManagementCard from './components/ClinicManagementCard.vue';
import { ClinicManagementKey, } from '@/keys/clinics-management/clinicManagementKeys';
import type { ClinicManagementRow } from '@/types/clinics-management/clinicManagement';

const props = defineProps<{
    clinics: ClinicManagementRow[];
}>();

const clinicsRef = ref<ClinicManagementRow[]>([
    ...(props.clinics ?? []),
]);

watch(
    () => props.clinics,
    (next) => {
        clinicsRef.value = [...(next ?? [])];
    },
    { deep: true },
);

function goManageClinic(clinicId: number) {
    router.visit(`/clinics-management/${clinicId}`, {
        preserveScroll: true,
    });
}

provide(ClinicManagementKey, {
    clinics: clinicsRef,
    goManageClinic,
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-10 w-full max-w-6xl px-4">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div
                    class="mb-5 border-b border-gray-200 pb-4"
                >
                    <h1
                        class="text-xl font-semibold tracking-tight text-gray-900"
                    >
                        Gerenciamento de Clínicas
                    </h1>

                    <p class="text-sm text-gray-500">
                        Visualize as clínicas e acompanhe pacientes
                        ativos e listas de espera.
                    </p>
                </div>

                <div
                    v-if="!clinicsRef.length"
                    class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
                >
                    Nenhuma clínica encontrada.
                </div>

                <div
                    v-else
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <ClinicManagementCard
                        v-for="clinic in clinicsRef"
                        :key="clinic.clinic_id"
                        :clinic="clinic"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>