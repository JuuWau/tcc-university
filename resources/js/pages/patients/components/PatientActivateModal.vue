<script setup lang="ts">
import ActivationButton from '@/components/buttons/ActivationButton.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import { PatientActivateKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const activateModal = inject(PatientActivateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!activateModal || !loading) {
    throw new Error('PatientActivateModal precisa estar dentro do provider');
}

function close() {
    activateModal.isOpen.value = false;
    activateModal.patient.value = null;
}

async function confirmActivate() {
    const patient = activateModal.patient.value;
    if (!patient || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/patients/activate/${patient.id}`);
        toast.success('Paciente ativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao ativar paciente',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="activateModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-bold text-gray-800">
                Ativar paciente
            </h2>
            <hr />

            <p class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja ativar o paciente
                <strong>{{
                    activateModal.patient.value?.name ??
                    activateModal.patient.value?.email
                }}</strong
                >?
            </p>

            <div class="flex justify-end gap-2 pt-6">
                <CancelButton @click="close" />
                <ActivationButton
                    type="button"
                    :disabled="loading"
                    class="flex items-center gap-2 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-60"
                    @click="confirmActivate"
                >
                    {{ loading ? 'Ativando...' : 'Ativar' }}
                </ActivationButton>
            </div>
        </div>
    </div>
</template>
