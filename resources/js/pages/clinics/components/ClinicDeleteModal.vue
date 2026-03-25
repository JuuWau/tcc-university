<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { ClinicDeleteKey, ClinicsGroupKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { Clinic } from '@/types/clinic/clinic';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(ClinicDeleteKey);
const clinics = inject<any>(ClinicsGroupKey);
const loading = inject(LoadingKey);

if (!deleteModal || !clinics || !loading) {
    throw new Error('ClinicDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function submit() {
    if (!deleteModal.clinic.value || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/clinics/${deleteModal.clinic.value.id}`);
        clinics.value = clinics.value.filter(
            (clinic: Clinic) => clinic.id !== deleteModal.clinic.value.id,
        );
        toast.success('Clínica removida com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao excluir clínica');
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
            <h2 class="mb-2 text-lg font-bold text-red-600">Excluir Clínica</h2>
            <hr />

            <p class="mb-6 pt-3 text-sm text-gray-600">
                Tem certeza que deseja excluir
                <strong>{{ deleteModal.clinic?.name }}</strong
                >?
                <br />
                Esta ação será bloqueada se existir histórico de agendas realizadas.
            </p>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <DeleteButton :loading="loading" @click="submit">
                    Excluir
                </DeleteButton>
            </div>
        </div>
    </div>
</template>
