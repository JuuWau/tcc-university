<script setup lang="ts">
import { inject, reactive } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { PatientClinicEnrollKey, RefreshTableKey } from '@/keys/patients/patientClinicsKeys';

const enrollModal = inject<any>(PatientClinicEnrollKey);
const refreshTableRef = inject(RefreshTableKey);

if (!enrollModal) {
    throw new Error(
        'PatientClinicsEnrollModal precisa estar dentro do provider'
    );
}

function close() {
    enrollModal.isOpen.value = false;
    enrollModal.patient.value = null;
}

async function submit() {
    if (!enrollModal.patient.value) return;
    
    try {
        await axios.post(
            `/patients/clinics/${enrollModal.clinicId.value}/enroll`,
            {
                patient_id: enrollModal.patient.value.id,
            }
        );

        toast.success(
            'Paciente inscrito com sucesso!'
        );
        refreshTableRef?.value?.();
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao inscrever paciente.'
        );
    }
}
</script>

<template>
    <div
        v-if="enrollModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">
                Inscrever paciente
            </h2>

            <hr />

            <div class="py-4">
                <p class="text-sm text-gray-500">
                Deseja mesmo inscrever
                <strong>
                        {{ enrollModal.patient.value?.name }}
                </strong>
                na clínica?
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />

                <SaveButton
                    @click.stop="submit"
                />
            </div>
        </div>
    </div>
</template>