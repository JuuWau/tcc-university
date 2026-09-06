<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white">
            <FormHeader 
                title="Remover inscrição" 
                subtitle="Confirme a remoção da inscrição do paciente." 
            />

            <div class="px-5 py-2">
                <p class="mb-6 pt-3 text-sm text-gray-600">
                    Tem certeza que deseja remover a inscrição de
                    <strong>
                        {{ removeModal.patient.value?.name }}
                    </strong>
                    da clínica?
                </p>
            </div>

            <FormFooter 
                :loading="loading" 
                action="delete" 
                action-label="Remover" 
                @cancel="close" 
                @delete="submit" />
        </div>
    </div>
</template>