<script setup lang="ts">
import { PrePatientsGroupKey, PrePatientCreateKey, PrePatientDeleteKey, PrePatientEditKey, RefreshTableKey, PrePatientConvertKey, } from '@/keys/pre-patients/prePatientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import PrePatientTable from './PrePatientTable.vue';
import PrePatientCreateModal from '@/pages/pre-patients/components/PrePatientCreateModal.vue';
import PrePatientDeleteModal from '@/pages/pre-patients/components/PrePatientDeleteModal.vue';
import PrePatientEditModal from '@/pages/pre-patients/components/PrePatientEditModal.vue';
import { PrePatient } from '@/types/pre-patients/prePatient';
import { provide, ref } from 'vue';
import PrePatientConvertModal from './components/PrePatientConvertModal.vue';

const loading = ref(false);

const prePatientsRef = ref<PrePatient[]>([]);

const refreshTableRef = ref<(() => void) | null>(null);

const createModal = {
    isOpen: ref(false),
};

const editModal = {
    isOpen: ref(false),
    prePatient: ref<PrePatient | null>(null),
};

const deleteModal = {
    isOpen: ref(false),
    prePatient: ref<PrePatient | null>(null),
};

const convertModal = {
    isOpen: ref(false),
    prePatient: ref<PrePatient | null>(null),
};

provide(PrePatientsGroupKey, prePatientsRef);
provide(RefreshTableKey, refreshTableRef);
provide(PrePatientCreateKey, createModal);
provide(PrePatientEditKey, editModal);
provide(PrePatientDeleteKey, deleteModal);
provide(PrePatientConvertKey, convertModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openEditModal(prePatient: PrePatient) {
    editModal.prePatient.value = prePatient;
    editModal.isOpen.value = true;
}

function openDeleteModal(prePatient: PrePatient) {
    deleteModal.prePatient.value = prePatient;
    deleteModal.isOpen.value = true;
}

function openConvertModal(prePatient: PrePatient) {
    convertModal.prePatient.value = prePatient;
    convertModal.isOpen.value = true;
}
</script>

<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <PrePatientCreateModal />
                        <PrePatientEditModal />
                        <PrePatientDeleteModal />
                        <PrePatientConvertModal />

                        <PrePatientTable
                            @edit="openEditModal"
                            @delete="openDeleteModal"
                            @create="openCreateModal"
                            @convert="openConvertModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>