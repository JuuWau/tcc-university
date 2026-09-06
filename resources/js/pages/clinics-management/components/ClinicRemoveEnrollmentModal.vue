<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { ClinicRemoveEnrollmentKey, RefreshTableKey } from '@/keys/clinics-management/clinicManagementShowKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const removeModal = inject<any>(ClinicRemoveEnrollmentKey);
const loading = inject(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

if (!removeModal || !loading) {
    throw new Error(
        'ClinicRemoveEnrollmentModal precisa estar dentro do provider'
    );
}

function close() {
    removeModal.isOpen.value = false;
    removeModal.patient.value = null;
}

async function submit() {
    if (!removeModal.patient.value || loading.value) return;

    try {
        loading.value = true;

        await axios.delete(
            `/clinics-management/${removeModal.clinicId.value}/remove-enrollment/${removeModal.patient.value.patient_id}`
        );

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