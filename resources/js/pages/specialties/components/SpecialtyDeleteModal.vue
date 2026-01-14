<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import {
    SpecialtiesGroupKey,
    SpecialtyDeleteKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { Specialty } from '@/types/specialty';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(SpecialtyDeleteKey);
const specialties = inject<any>(SpecialtiesGroupKey);
const loading = inject(LoadingKey);

if (!deleteModal || !specialties || !loading) {
    throw new Error('SpecialtyDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function confirmDelete() {
    if (!deleteModal.specialty.value || loading.value) return;

    try {
        loading.value = true;

        await axios.delete(`/specialties/${deleteModal.specialty.value.id}`);

        specialties.value = specialties.value.filter(
            (s: Specialty) => s.id !== deleteModal.specialty.value.id,
        );

        toast.success('Especialidade removida com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao remover especialidade',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="deleteModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold text-red-600">
                Excluir Especialidade
            </h2>
            <hr />

            <p class="mb-6 pt-3 text-sm text-gray-600">
                Tem certeza que deseja excluir
                <strong>{{ deleteModal.specialty?.name }}</strong
                >?
                <br />
                Esta ação não poderá ser desfeita.
            </p>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <DeleteButton
                    :loading="loading"
                    class="bg-red-600 hover:bg-red-700"
                    @click="confirmDelete"
                >
                    Excluir
                </DeleteButton>
            </div>
        </div>
    </div>
</template>
