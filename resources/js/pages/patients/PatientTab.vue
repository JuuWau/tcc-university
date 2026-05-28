<template>
    <AppLayout>
        <div class="w-full space-y-8 p-8">
            <PatientHeader />

            <nav class="flex flex-wrap gap-2 rounded-2xl bg-gray-100 p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="
                        activeTab === tab.key
                            ? 'bg-white text-sky-600 shadow'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                >
                    {{ tab.label }}
                </button>
            </nav>

            <div>
                <PatientPersonalData v-if="activeTab === 'personal'" />
                <PatientSchedules v-if="activeTab === 'schedules'" />
            </div>
        </div>

        <PatientPersonalDataEditModal @updated="onPersonalDataUpdated" />
        <PatientStudentEditModal @updated="onStudentUpdated" />
    </AppLayout>
</template>

<script setup lang="ts">
import type { PatientTabContext } from '@/keys/patients/patientKeys';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientPersonalDataEditModal from '@/pages/patients/components/PatientPersonalDataEditModal.vue';
import PatientStudentEditModal from '@/pages/patients/components/PatientStudentEditModal.vue';
import PatientHeader from '@/pages/patients/PatientHeader.vue';
import PatientPersonalData from '@/pages/patients/tabs/PatientPersonalData.vue';
import PatientSchedules from '@/pages/patients/tabs/PatientSchedules.vue';
import type { PatientForTab } from '@/types/patient/patient';
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref } from 'vue';

const page = usePage();
const patient = computed(
    () => (page.props as unknown as { patient: PatientForTab }).patient,
);

const students = computed(
    () =>
        (page.props as unknown as { students?: StudentOption[] }).students ??
        [],
);

const editPersonalDataModalOpen = ref(false);
const editStudentModalOpen = ref(false);

provide(PatientTabContextKey, {
    patient,
    editPersonalDataModalOpen,
    editStudentModalOpen,
    students,
} as PatientTabContext);

type TabKey = 'personal' | 'schedules';
const activeTab = ref<TabKey>('personal');

const tabs: { key: TabKey; label: string }[] = [
    { key: 'personal', label: 'Dados pessoais' },
    { key: 'schedules', label: 'Agendamentos' },
];

function onPersonalDataUpdated() {
    editPersonalDataModalOpen.value = false;
    void router.reload();
}

function onStudentUpdated() {
    editStudentModalOpen.value = false;
    void router.reload();
}
</script>
