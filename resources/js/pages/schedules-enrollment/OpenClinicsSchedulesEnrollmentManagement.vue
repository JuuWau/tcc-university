<script setup lang="ts">
import {
    OpenClinicsManagementScheduleKey,
    type OpenClinicScheduleRow,
} from '@/keys/schedule-enrollment/openClinicsScheduleManagementKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import OpenClinicCard from '@/pages/schedules-enrollment/components/OpenClinicScheduleEnrollmentCard.vue';
import { router } from '@inertiajs/vue3';
import { provide, ref, watch } from 'vue';

const props = defineProps<{
    clinics: OpenClinicScheduleRow[];
}>();

const clinicsRef = ref<OpenClinicScheduleRow[]>([...(props.clinics ?? [])]);

watch(
    () => props.clinics,
    (next) => {
        clinicsRef.value = [...(next ?? [])];
    },
    { deep: true },
);

function goManageClinicSchedule(clinicId: number) {
    router.visit(`/schedule-enrollment/open-clinic/${clinicId}`, {
        preserveScroll: true,
    });
}

provide(OpenClinicsManagementScheduleKey, {
    clinics: clinicsRef,
    goManageClinicSchedule,
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-10 w-full max-w-6xl px-4">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div
                    class="mb-5 flex flex-col gap-3 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-xl font-semibold tracking-tight text-gray-900"
                        >
                            Clínicas com agenda aberta
                        </h1>
                        <p class="text-sm text-gray-500">
                            Visualize as clínicas que possuem dias abertos para o seu período e
                            acompanhe a próxima disponibilidade.
                        </p>
                    </div>
                </div>

                <div
                    v-if="!clinicsRef.length"
                    class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
                >
                    Nenhuma clínica com agenda aberta no momento.
                </div>

                <div
                    v-else
                    class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <OpenClinicCard
                        v-for="clinic in clinicsRef"
                        :key="clinic.clinic_id"
                        :clinic="clinic"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
