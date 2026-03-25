<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { PatientDeleteKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject(PatientDeleteKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deleteModal || !loading) {
    throw new Error('PatientDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
    deleteModal.patient.value = null;
}

async function confirmDelete() {
    const patient = deleteModal.patient.value;
    if (!patient || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/patients/${patient.id}`);
        toast.success('Paciente excluído com sucesso');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao excluir paciente',
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
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
            <h2 class="mb-2 text-lg font-bold text-red-600">
                Excluir paciente
            </h2>
            <hr />

            <p class="mb-6 pt-3 text-sm text-gray-600">
                Tem certeza que deseja excluir
                <strong>{{
                    deleteModal.patient.value?.name ??
                    deleteModal.patient.value?.email
                }}</strong
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
