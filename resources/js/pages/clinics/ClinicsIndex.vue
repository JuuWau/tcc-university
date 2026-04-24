<template>
    <AppLayout>
        <div class="mt-25 flex justify-center">
            <div class="w-full max-w-7xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <ClinicEditModal />
                        <ClinicCreateModal />
                        <ClinicActivateModal />
                        <ClinicDeactivateModal />
                        <ClinicDeleteModal />

                        <ClinicsTable
                            @create="openCreateModal"
                            @edit="openEditModal"
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
    ClinicActivateKey,
    ClinicCreateKey,
    ClinicDeactivateKey,
    ClinicDeleteKey,
    ClinicEditKey,
    ClinicsGroupKey,
} from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import ClinicsTable from '@/pages/clinics/ClinicsTable.vue';
import ClinicActivateModal from '@/pages/clinics/components/ClinicActivateModal.vue';
import ClinicCreateModal from '@/pages/clinics/components/ClinicCreateModal.vue';
import ClinicDeactivateModal from '@/pages/clinics/components/ClinicDeactivateModal.vue';
import ClinicDeleteModal from '@/pages/clinics/components/ClinicDeleteModal.vue';
import ClinicEditModal from '@/pages/clinics/components/ClinicEditModal.vue';
import type { Clinic } from '@/types/clinic/clinic';
import { provide, ref } from 'vue';

const { clinics } = defineProps({
    clinics: Array,
});

const clinicsRef = ref(clinics as Clinic[]);
const loading = ref(false);

const createModal = { isOpen: ref(false) };
const editModal = {
    isOpen: ref(false),
    clinic: ref<Clinic | null>(null),
};
const deactivateModal = {
    isOpen: ref(false),
    clinic: ref<Clinic | null>(null),
};
const activateModal = {
    isOpen: ref(false),
    clinic: ref<Clinic | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    clinic: ref<Clinic | null>(null),
};

provide(ClinicsGroupKey, clinicsRef);
provide(ClinicCreateKey, createModal);
provide(ClinicEditKey, editModal);
provide(ClinicDeactivateKey, deactivateModal);
provide(ClinicActivateKey, activateModal);
provide(ClinicDeleteKey, deleteModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openEditModal(clinic: Clinic) {
    editModal.clinic.value = clinic;
    editModal.isOpen.value = true;
}

function openDeactivateModal(clinic: Clinic) {
    deactivateModal.clinic.value = clinic;
    deactivateModal.isOpen.value = true;
}

function openActivateModal(clinic: Clinic) {
    activateModal.clinic.value = clinic;
    activateModal.isOpen.value = true;
}

function openDeleteModal(clinic: Clinic) {
    deleteModal.clinic.value = clinic;
    deleteModal.isOpen.value = true;
}
</script>
