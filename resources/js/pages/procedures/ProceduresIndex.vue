<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <ProcedureEditModal />
                        <ProcedureCreateModal />
                        <ProcedureDeleteModal />
                        <ProceduresTable
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
    ProcedureCreateKey,
    ProcedureDeleteKey,
    ProcedureEditKey,
    ProceduresGroupKey,
    ProceduresSpecialtiesKey,
} from '@/keys/procedures/procedureKeys';
import type { ProcedureSpecialtyOption } from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import ProceduresTable from '@/pages/procedures/ProceduresTable.vue';
import ProcedureCreateModal from '@/pages/procedures/components/ProcedureCreateModal.vue';
import ProcedureDeleteModal from '@/pages/procedures/components/ProcedureDeleteModal.vue';
import ProcedureEditModal from '@/pages/procedures/components/ProcedureEditModal.vue';
import type { Procedure } from '@/types/procedure';
import { usePage } from '@inertiajs/vue3';
import { provide, ref, watch } from 'vue';

const page = usePage();
const proceduresRef = ref<Procedure[]>([...(page.props.procedures as Procedure[] ?? [])]);
const specialties = (page.props.specialties as ProcedureSpecialtyOption[]) ?? [];

watch(
    () => page.props.procedures as Procedure[] | undefined,
    (procedures) => {
        proceduresRef.value = procedures ?? [];
    },
    { immediate: false },
);

const loading = ref(false);

const createModal = { isOpen: ref(false) };
const editModal = {
    isOpen: ref(false),
    procedure: ref<Procedure | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    procedure: ref<Procedure | null>(null),
};

provide(ProceduresGroupKey, proceduresRef);
provide(ProceduresSpecialtiesKey, specialties);
provide(ProcedureEditKey, editModal);
provide(ProcedureDeleteKey, deleteModal);
provide(ProcedureCreateKey, createModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openEditModal(procedure: Procedure) {
    editModal.procedure.value = procedure;
    editModal.isOpen.value = true;
}

function openDeleteModal(procedure: Procedure) {
    deleteModal.procedure.value = procedure;
    deleteModal.isOpen.value = true;
}
</script>
