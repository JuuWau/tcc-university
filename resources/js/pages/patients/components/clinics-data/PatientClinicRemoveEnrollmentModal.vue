<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { PatientClinicRemoveEnrollmentKey, RefreshTableKey } from '@/keys/patients/patientClinicsKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject, ref } from 'vue';
import { toast } from 'vue3-toastify';

const removeModal = inject(PatientClinicRemoveEnrollmentKey);
const loading = inject(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

const hasDependencies = !!removeModal && !!loading;

if (!hasDependencies) {
    console.error('Missing dependencies in PatientClinicRemoveEnrollmentModal');
}

function close() {
    removeModal.isOpen.value = false;
    removeModal.patient.value = null;
}

async function submit() {
    if (!removeModal.patient.value || loading.value) return;

    try {
        loading.value = true;

        const patientId = removeModal.patient.value?.id;

        await axios.delete(
            `/patients/${patientId}/clinics/${removeModal.clinicId.value}/remove-enrollment`
        );

        console.log('DELETE OK');
        console.log('refreshTableRef', refreshTableRef);
        console.log('refreshTableRef.value', refreshTableRef?.value);

        toast.success('Inscrição removida com sucesso');
        refreshTableRef?.value?.();
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao remover inscrição'
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="removeModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold text-red-600">
                Remover inscrição
            </h2>

            <hr />

            <p class="mb-6 pt-3 text-sm text-gray-600">
                Tem certeza que deseja remover a inscrição de
                <strong>
                    {{ removeModal.patient.value?.name }}
                </strong>
                da clínica?
            </p>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />

                <DeleteButton
                    :loading="loading"
                    @click="submit"
                >
                    Remover
                </DeleteButton>
            </div>
        </div>
    </div>
</template>