<script setup lang="ts">
import { inject, onMounted, provide, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientClinicsTable from './PatientClinicsTable.vue';
import PatientClinicStatusFilter from '../../components/clinics-data/PatientClinicStatusFilter.vue';
import { usePatientClinics } from '@/composables/patient/usePatientClinics';
import { PatientClinicCreateWaitingListKey, PatientClinicEnrollKey, PatientClinicRemoveEnrollmentKey, PatientClinicsKey, RefreshTableKey } from '@/keys/patients/patientClinicsKeys';
import { PatientTabContext, PatientTabContextKey } from '@/keys/patients/patientKeys.js';
import { PatientForTab } from '@/types/patient/patient.js';
import PatientClinicEnrollModal from '../../components/clinics-data/PatientClinicEnrollModal.vue';
import PatientClinicRemoveEnrollmentModal from '../../components/clinics-data/PatientClinicRemoveEnrollmentModal.vue';
import { LoadingKey } from '@/keys/ui/loadingKey.js';
import CreateButton from '@/components/buttons/CreateButton.vue';
import PatientClinicCreateWaitingListModal from '../../components/clinics-data/PatientClinicCreateWaitingListModal.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const can = (permission: string) => {
    return page.props.auth.permissions.includes(permission);
};

const patientContext = inject(PatientTabContextKey) as PatientTabContext | undefined;

const loading = ref(false);
const refreshTableRef = ref<(() => void) | null>(null);

if (!patientContext) {
    throw new Error(
        'PatientClinicsData must be used inside a PatientTab (provide PatientTabContextKey).'
    );
}

const { patient } = patientContext;

const patientClinics = usePatientClinics();
console.log(patient, patientClinics);

const enrollModal = {
    isOpen: ref(false),
    patient: ref<PatientForTab | null>(null),
    clinicId: ref<number | null>(null),
};

const removeEnrollmentModal = {
    isOpen: ref(false),
    patient: ref<PatientForTab | null>(null),
    clinicId: ref<number | null>(null),
};

const createWaitingListModal = {
    isOpen: ref(false),
    patient: ref<PatientForTab | null>(null),
};

provide(PatientClinicsKey, patientClinics);
provide(PatientClinicEnrollKey, enrollModal);
provide(PatientClinicRemoveEnrollmentKey, removeEnrollmentModal);
provide(PatientClinicCreateWaitingListKey, createWaitingListModal);
provide(RefreshTableKey, refreshTableRef);
provide(LoadingKey, loading);

if (!patientContext) {
    throw new Error(
        'PatientClinicsData must be used inside a PatientTab (provide PatientTabContextKey).'
    );
}

function openEnrollModal(clinic: any) {
    enrollModal.patient.value = patient.value;
    enrollModal.clinicId.value = clinic.clinic_id;
    enrollModal.isOpen.value = true;
}

function openRemoveEnrollmentModal(clinic: any) {
    removeEnrollmentModal.patient.value = patient.value;
    removeEnrollmentModal.clinicId.value = clinic.clinic_id;
    removeEnrollmentModal.isOpen.value = true;
}

function openCreateWaitingListModal() {
    createWaitingListModal.patient.value = patient.value;
    createWaitingListModal.isOpen.value = true;
}

function refetch() {
    return patientClinics.loadClinics(patient.value.id);
}

watch(
    [
        patientClinics.page,
        patientClinics.activeStatus,
    ],
    () => {
        if (patient.value?.id) {
            patientClinics.loadClinics(
                patient.value.id
            );
        }
    }
);

onMounted(() => {
    patientClinics.loadClinics(patient.value.id);
    if (refreshTableRef) {
        refreshTableRef.value = refetch;
    }
});
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2
                    class="text-lg font-semibold text-gray-900"
                >
                    Clínicas
                </h2>
                <p class="text-sm text-gray-500">
                    Visualize as clínicas em que o paciente está inscrito ou aguardando vaga.
                </p>
            </div>

            <CreateButton
                v-if="patientClinics.activeStatus.value === 'waiting' && can('patients.personal-page.addPatientToWaitingList')"
                label="Adicionar paciente a lista de espera"
                icon="Plus"
                @click="openCreateWaitingListModal"
            >
                Adicionar paciente à lista de espera
            </CreateButton>
        </div>

        <PatientClinicStatusFilter />
        <PatientClinicEnrollModal />
        <PatientClinicRemoveEnrollmentModal />
        <PatientClinicCreateWaitingListModal />

        <PatientClinicsTable 
            @enroll="openEnrollModal"
            @remove="openRemoveEnrollmentModal"
        />
    </div>
</template>