<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeactivateButton from '@/components/buttons/DeactivateButton.vue';
import {
    ClinicDeactivateKey,
    ClinicsGroupKey,
} from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { Clinic } from '@/types/clinic/clinic';
import axios from 'axios';
import { inject, type Ref } from 'vue';
import { toast } from 'vue3-toastify';

const deactivateModal = inject<any>(ClinicDeactivateKey);
const clinics = inject<any>(ClinicsGroupKey);
const loading = inject<Ref<boolean>>(LoadingKey)!;

if (!deactivateModal || !clinics) {
    throw new Error('ClinicDeactivateModal precisa estar dentro do provider');
}

function close() {
    deactivateModal.isOpen.value = false;
}

async function submit() {
    if (!deactivateModal.clinic.value || loading.value) return;

    try {
        loading.value = true;
        await axios.patch(
            `/clinics/${deactivateModal.clinic.value.id}/deactivate`,
            {
                confirm: true,
            },
        );

        const index = clinics.value.findIndex(
            (clinic: Clinic) => clinic.id === deactivateModal.clinic.value.id,
        );
        if (index !== -1) clinics.value[index].active = false;

        toast.success('Clínica inativada com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao inativar clínica',
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
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold text-amber-600">
                Inativar Clínica
            </h2>
            <hr />

            <p class="mb-6 pt-3 text-sm text-gray-600">
                Ao inativar esta clínica, os registros subsequentes relacionados
                em agenda e inscrições serão inativados.
            </p>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <DeactivateButton
                    :loading="loading"
                    class="bg-amber-600 hover:bg-amber-700"
                    @click="submit"
                >
                    Inativar
                </DeactivateButton>
            </div>
        </div>
    </div>
</template>
