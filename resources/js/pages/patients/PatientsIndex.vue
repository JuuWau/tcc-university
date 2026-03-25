<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <PatientCreateModal />
                        <PatientDeactivateModal />
                        <PatientDeleteModal />
                        <PatientActivateModal />

                        <PatientsTable
                            @create="openCreateModal"
                            @view="openViewModal"
                            @deactivate="openDeactivateModal"
                            @activate="openActivateModal"
                            @delete="openDeleteModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    PatientActivateKey,
    PatientCreateKey,
    PatientDeactivateKey,
    PatientDeleteKey,
    RefreshTableKey,
} from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientActivateModal from '@/pages/patients/components/PatientActivateModal.vue';
import PatientCreateModal from '@/pages/patients/components/PatientCreateModal.vue';
import PatientDeactivateModal from '@/pages/patients/components/PatientDeactivateModal.vue';
import PatientDeleteModal from '@/pages/patients/components/PatientDeleteModal.vue';
import PatientsTable from '@/pages/patients/PatientsTable.vue';
import type { PatientWithInvite } from '@/types/patient/patient';
import { router } from '@inertiajs/vue3';
import { provide, ref } from 'vue';

const loading = ref(false);
const refreshTableRef = ref<(() => void) | null>(null);

const createModal = { isOpen: ref(false) };
const deactivateModal = {
    isOpen: ref(false),
    patient: ref<PatientWithInvite | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    patient: ref<PatientWithInvite | null>(null),
};
const activateModal = {
    isOpen: ref(false),
    patient: ref<PatientWithInvite | null>(null),
};

provide(RefreshTableKey, refreshTableRef);
provide(PatientCreateKey, createModal);
provide(PatientDeactivateKey, deactivateModal);
provide(PatientDeleteKey, deleteModal);
provide(PatientActivateKey, activateModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openViewModal(patient: PatientWithInvite) {
    router.visit(`/patients/${patient.id}`);
}

function openDeactivateModal(patient: PatientWithInvite) {
    deactivateModal.patient.value = patient;
    deactivateModal.isOpen.value = true;
}

function openDeleteModal(patient: PatientWithInvite) {
    deleteModal.patient.value = patient;
    deleteModal.isOpen.value = true;
}

function openActivateModal(patient: PatientWithInvite) {
    activateModal.patient.value = patient;
    activateModal.isOpen.value = true;
}
</script>
