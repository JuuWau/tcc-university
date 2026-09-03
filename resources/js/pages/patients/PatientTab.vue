<template>
    <AppLayout>
        <div class="w-full space-y-8 p-8">
            <PatientHeader />

            <nav class="flex flex-wrap gap-2 rounded-2xl bg-gray-100 p-1">
                <button
                    v-for="tab in visibleTabs"
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
                <PatientPersonalData
                    v-if="activeTab === 'personal'"
                />

                <PatientSchedules
                    v-if="
                        activeTab === 'schedules' &&
                        can('patients.personal-page.viewAppointments')
                    "
                />

                <PatientClinicsData
                    v-if="
                        activeTab === 'clinics' &&
                        can('patients.personal-page.viewClinics')
                    "
                />

                <PatientActionLogs
                    v-if="
                        activeTab === 'logs' &&
                        can('action-logs.view')
                    "
                />
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
import PatientPersonalDataEditModal from '@/pages/patients/components/personal-data/PatientPersonalDataEditModal.vue';
import PatientStudentEditModal from '@/pages/patients/components/student-data/PatientStudentEditModal.vue';
import PatientHeader from '@/pages/patients/PatientHeader.vue';
import PatientPersonalData from '@/pages/patients/tabs/PatientPersonalData.vue';
import PatientSchedules from '@/pages/patients/tabs/patient-schedule/PatientSchedules.vue';
import type { PatientForTab } from '@/types/patient/patient';
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import PatientClinicsData from './tabs/clinics-data/PatientClinicsData.vue';
import PatientActionLogs from '@/pages/users/tabs/UserActionLogs.vue';
import { useUserActionLogs } from '@/composables/user/useUserActionLogs.js';
import { UserActionLogsContextKey } from '@/keys/action-logs/userActionLogsKeys.js';

const page = usePage();

const patient = computed(
    () => (page.props as unknown as { patient: PatientForTab }).patient,
);

const students = computed(
    () =>
        (page.props as unknown as { students?: StudentOption[] }).students ??
        [],
);

const can = (permission: string) => {
    const props = page.props as unknown as {
        auth: {
            permissions: string[];
        };
    };

    return props.auth.permissions.includes(permission);
};

const editPersonalDataModalOpen = ref(false);
const editStudentModalOpen = ref(false);

const actionLogs = useUserActionLogs(
    'patients',
    computed(() => patient.value.id),
);

provide(UserActionLogsContextKey, actionLogs);

provide(PatientTabContextKey, {
    patient,
    editPersonalDataModalOpen,
    editStudentModalOpen,
    students,
} as PatientTabContext);

type TabKey = 'personal' | 'schedules' | 'clinics' | 'logs';
const activeTab = ref<TabKey>('personal');

const tabs: { key: TabKey; label: string, permission?: string; }[] = [
    { key: 'personal', label: 'Dados pessoais' },
    { key: 'schedules', label: 'Agendamentos' },
    { key: 'clinics', label: 'Clínicas' },
    { key: 'logs', label: 'Histórico de ações', permission: 'action-logs.view' },
];

const visibleTabs = computed(() => {
    return tabs.filter((tab) => {
        return !tab.permission || can(tab.permission);
    });
});

function onPersonalDataUpdated() {
    editPersonalDataModalOpen.value = false;
    void router.reload();
}

function onStudentUpdated() {
    editStudentModalOpen.value = false;
    void router.reload();
}

watch(activeTab, async (tab) => {
    if (
        tab === 'logs' &&
        can('action-logs.view') &&
        actionLogs.logs.value.data.length === 0
    ) {
        await actionLogs.load();
    }
});
</script>
