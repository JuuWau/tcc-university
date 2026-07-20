<script setup lang="ts">
import { onMounted, provide, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClinicStatusFilter from './components/ClinicStatusFilter.vue';
import ClinicPatientsTable from './ClinicPatientsTable.vue';
import { useClinicManagement } from '@/composables/clinics-management/useClinicManagement';
import { ClinicCreateWaitingListKey, ClinicEnrollKey, ClinicManagementShowKey, ClinicRemoveEnrollmentKey, RefreshTableKey, } from '@/keys/clinics-management/clinicManagementShowKeys';
import type { PatientForTab  } from '@/types/patient/patient';
import ClinicEnrollModal from './components/ClinicEnrollModal.vue';
import ClinicRemoveModal from './components/ClinicRemoveEnrollmentModal.vue';
import { LoadingKey } from '@/keys/ui/loadingKey.js';
import CreateButton from '@/components/buttons/CreateButton.vue';
import ClinicCreateWaitingListModal from './components/ClinicCreateWaitingListModal.vue';

const props = defineProps<{
    clinic: {
        id: number;
        name: string;
    };
}>();

const loading = ref(false);
const refreshTableRef = ref<(() => void) | null>(null);
const clinicManagement = useClinicManagement();

const enrollModal = {
    isOpen: ref(false),
    patient: ref<PatientForTab | null>(null),
    clinicId: ref<number | null>(props.clinic.id),
};

const removeEnrollmentModal = {
    isOpen: ref(false),
    patient: ref<PatientForTab | null>(null),
    clinicId: ref(props.clinic.id),
};

const createWaitingListModal = {
    isOpen: ref(false),
    clinicId: ref(props.clinic.id),
};

provide(ClinicEnrollKey, enrollModal);
provide(ClinicRemoveEnrollmentKey, removeEnrollmentModal);
provide(ClinicCreateWaitingListKey, createWaitingListModal);
provide(LoadingKey, loading);
provide(RefreshTableKey, refreshTableRef);
provide(ClinicManagementShowKey, clinicManagement,);

function openEnrollModal(patient: PatientForTab ) {
    enrollModal.patient.value = patient;
    enrollModal.isOpen.value = true;
}

function openRemoveEnrollmentModal(patient: PatientForTab) {
    removeEnrollmentModal.patient.value = patient;
    removeEnrollmentModal.isOpen.value = true;
}

function openCreateWaitingListModal() {
    createWaitingListModal.isOpen.value = true;
}

function refetch() {
    return clinicManagement.loadPatients(props.clinic.id);
}


onMounted(() => {
    clinicManagement.loadPatients(props.clinic.id);
});

watch(
    [
        clinicManagement.page,
        clinicManagement.activeStatus,
    ],
    () => {
        clinicManagement.loadPatients(props.clinic.id);
    }
);

onMounted(() => {
    clinicManagement.loadPatients(props.clinic.id);
    if (refreshTableRef) {
        refreshTableRef.value = refetch;
    }
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-10 w-full max-w-7xl px-4">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div
                    class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                            Pacientes
                        </h1>

                        <p class="text-sm text-gray-500">
                            Gerencie pacientes inscritos e lista de espera da clínica.
                        </p>
                    </div>

                    <CreateButton
                        v-if="clinicManagement.activeStatus.value === 'waiting'"
                        label="Adicionar paciente a lista de espera"
                        icon="Plus"
                        @click="openCreateWaitingListModal"
                    >
                        Adicionar paciente à lista de espera
                    </CreateButton>
                </div>
                <ClinicEnrollModal />
                <ClinicRemoveModal />
                <ClinicStatusFilter />
                <ClinicCreateWaitingListModal />

                <ClinicPatientsTable 
                    @enroll="openEnrollModal"
                    @remove="openRemoveEnrollmentModal"
                />
            </div>
        </div>
    </AppLayout>
</template>