<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import AppMultiselect from '@/components/AppMultiselect.vue';

import {
PatientClinicCreateWaitingListKey,
    RefreshTableKey,
} from '@/keys/patients/patientClinicsKeys';

import { LoadingKey } from '@/keys/ui/loadingKey';

import axios from 'axios';
import { inject, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

type ClinicOption = {
    label: string;
    value: number;
};

const modal = inject(PatientClinicCreateWaitingListKey);
const loading = inject<any>(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

if (!modal) {
    throw new Error(
        'PatientClinicEnrollModal precisa estar dentro do provider'
    );
}

const clinicOptions = ref<ClinicOption[]>([]);
const selectedClinic = ref<ClinicOption | null>(null);
const loadingClinics = ref(false);

watch(
    () => modal.isOpen.value,
    async (isOpen: boolean) => {
        if (!isOpen) {
            return;
        }

        selectedClinic.value = null;
        console.log('ola')
        await loadClinics();
    }
);

async function loadClinics() {
    try {
        loadingClinics.value = true;

        const { data } = await axios.get(
            `/patients/${modal.patient.value.id}/available-clinics`
        );

        clinicOptions.value = data ?? [];
    } catch {
        toast.error(
            'Erro ao carregar clínicas'
        );
    } finally {
        loadingClinics.value = false;
    }
}

function close() {
    modal.isOpen.value = false;
    modal.patient.value = null;
    selectedClinic.value = null;
}

async function submit() {
    if (!selectedClinic.value) {
        toast.error(
            'Selecione uma clínica'
        );

        return;
    }

    try {
        loading.value = true;
        
        await axios.post(`/patients/clinics/${selectedClinic.value}/waiting-list`,
                {
                        patient_id: modal.patient.value.id,
                }
        );

        toast.success(
            'Paciente inscrito com sucesso'
        );

        refreshTableRef?.value?.();

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao inscrever paciente'
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div
            class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl"
        >
            <div class="mb-4">
                <h2
                    class="text-lg font-bold text-gray-900"
                >
                    Inscrever paciente
                </h2>

                <p
                    class="text-sm text-gray-500"
                >
                    Selecione a clínica para inscrição.
                </p>
            </div>

            <hr />

            <div class="py-6">
                <label
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Clínica
                </label>

                <AppMultiselect
                    v-model="selectedClinic"
                    :options="clinicOptions"
                    :loading="loadingClinics"
                    :searchable="true"
                    :can-clear="true"
                    :close-on-select="true"
                    label="label"
                    value-prop="value"
                    placeholder="Selecione uma clínica"
                />
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton
                    @click="close"
                />

                <SaveButton
                    :loading="loading"
                    @click="submit"
                >
                    Inscrever
                </SaveButton>
            </div>
        </div>
    </div>
</template>