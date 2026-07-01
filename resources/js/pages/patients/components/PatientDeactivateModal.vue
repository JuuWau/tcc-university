<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { PatientDeactivateKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import DeactivateButton from '@/components/buttons/DeactivateButton.vue';

const deactivateModal = inject(PatientDeactivateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deactivateModal || !loading) {
    throw new Error('PatientDeactivateModal precisa estar dentro do provider');
}

function close() {
    deactivateModal.isOpen.value = false;
    deactivateModal.patient.value = null;
}

async function confirmDeactivate() {
    const patient = deactivateModal.patient.value;
    if (!patient || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/patients/deactivate/${patient.id}`);
        toast.success('Paciente inativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao inativar paciente',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="deactivateModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-bold text-gray-800">
                Inativar paciente
            </h2>
            <hr />

            <p class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja inativar o paciente
                <strong>{{
                    deactivateModal.patient.value?.name ??
                    deactivateModal.patient.value?.email
                }}</strong
                >?
            </p>

            <div class="flex justify-end gap-2 pt-6">
                <CancelButton @click="close" />
                <DeactivateButton
                    type="button"
                    @click="confirmDeactivate"
                >
                    {{ loading ? 'Inativando...' : 'Inativar' }}
                </DeactivateButton>
            </div>
        </div>
    </div>
</template>
