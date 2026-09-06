<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Excluir paciente"
                subtitle="Confirme a exclusão do paciente."
            />

            <div class="px-6 py-5">
                <p class="text-sm text-gray-600">
                    Tem certeza que deseja excluir
                    <strong class="font-semibold text-gray-900">
                        {{
                            deleteModal.patient.value?.name ??
                            deleteModal.patient.value?.email
                        }}
                    </strong>
                    ? <br />
                    <span class="mt-1 block text-red-700">
                        Esta ação não poderá ser desfeita.
                    </span>
                </p>
            </div>
            
            <FormFooter
                :loading="loading"
                action="delete"
                action-label="Excluir"
                @cancel="close"
                @delete="confirmDelete"
            />
        </div>
    </div>
</template>
