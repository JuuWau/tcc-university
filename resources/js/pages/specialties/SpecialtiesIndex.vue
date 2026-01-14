<template>
    <AppLayout>
        <div class="mt-25 flex justify-center">
            <div class="w-full max-w-7xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <SpecialtyEditModal />

                        <SpecialtyCreateModal />

                        <SpecialtyDeleteModal />

                        <SpecialtiesTable
                            @edit="openEditModal"
                            @delete="openDeleteModal"
                            @create="openCreateModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    SpecialtiesGroupKey,
    SpecialtyCreateKey,
    SpecialtyDeleteKey,
    SpecialtyEditKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import SpecialtiesTable from '@/pages/specialties/SpecialtiesTable.vue';
import SpecialtyCreateModal from '@/pages/specialties/components/SpecialtyCreateModal.vue';
import SpecialtyDeleteModal from '@/pages/specialties/components/SpecialtyDeleteModal.vue';
import SpecialtyEditModal from '@/pages/specialties/components/SpecialtyEditModal.vue';
import { Specialty } from '@/types/specialty';
import { provide, ref } from 'vue';
const { specialties } = defineProps({
    specialties: Array,
});

const specialtiesRef = ref(specialties);
const loading = ref(false);

const createModal = { isOpen: ref(false) };
const editModal = {
    isOpen: ref(false),
    specialty: ref<Specialty | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    specialty: ref<Specialty | null>(null),
};

provide(SpecialtiesGroupKey, specialtiesRef);
provide(SpecialtyEditKey, editModal);
provide(SpecialtyDeleteKey, deleteModal);
provide(SpecialtyCreateKey, createModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openEditModal(specialty: Specialty) {
    editModal.specialty.value = specialty;
    editModal.isOpen.value = true;
}

function openDeleteModal(specialty: Specialty) {
    deleteModal.specialty.value = specialty;
    deleteModal.isOpen.value = true;
}
</script>
