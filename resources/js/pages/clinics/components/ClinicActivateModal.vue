<script setup lang="ts">
import ActivationButton from '@/components/buttons/ActivationButton.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import { ClinicActivateKey, ClinicsGroupKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { Clinic } from '@/types/clinic/clinic';
import axios from 'axios';
import { inject, type Ref } from 'vue';
import { toast } from 'vue3-toastify';

const activateModal = inject<any>(ClinicActivateKey);
const clinics = inject<any>(ClinicsGroupKey);
const loading = inject<Ref<boolean>>(LoadingKey)!;

if (!activateModal || !clinics) {
    throw new Error('ClinicActivateModal precisa estar dentro do provider');
}

function close() {
    activateModal.isOpen.value = false;
}

async function submit() {
    if (!activateModal.clinic.value || loading.value) return;

    try {
        loading.value = true;
        await axios.patch(`/clinics/${activateModal.clinic.value.id}/activate`);

        const index = clinics.value.findIndex(
            (clinic: Clinic) => clinic.id === activateModal.clinic.value.id,
        );
        if (index !== -1) clinics.value[index].active = true;

        toast.success('Clínica ativada com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao ativar clínica');
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
            <h2 class="mb-4 text-lg font-bold text-gray-800">Ativar clínica</h2>
            <hr />

            <p class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja ativar a clínica
                <strong>{{ activateModal.clinic.value?.name }}</strong
                >?
            </p>

            <div class="flex justify-end gap-2 pt-6">
                <CancelButton @click="close" />
                <ActivationButton
                    type="button"
                    :disabled="loading"
                    @click="submit"
                >
                    {{ loading ? 'Ativando...' : 'Ativar' }}
                </ActivationButton>
            </div>
        </div>
    </div>
</template>
