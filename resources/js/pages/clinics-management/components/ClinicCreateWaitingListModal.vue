<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import AppMultiselect from '@/components/AppMultiselect.vue';
import { ClinicCreateWaitingListKey, RefreshTableKey } from '@/keys/clinics-management/clinicManagementShowKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import { X } from 'lucide-vue-next';

type PatientOption = {
    label: string;
    value: number;
};

const modal = inject<any>(ClinicCreateWaitingListKey);
const refreshTableRef = inject(RefreshTableKey);

if (!modal) {
    throw new Error(
        'ClinicCreateWaitingListModal precisa estar dentro do provider'
    );
}

const loading = inject<any>(LoadingKey);

const form = reactive({
    patients: [] as PatientOption[],
});

const selectedPatient = ref<PatientOption | null>(null);
const patientOptions = ref<PatientOption[]>([]);
const loadingPatients = ref(false);

const availablePatients = computed(() => {
    return patientOptions.value.filter(
        (patient) =>
            !form.patients.some(
                (selected) => selected.value === patient.value
            )
    );
});

watch(
    () => modal.isOpen.value,
    async (isOpen: boolean) => {
        if (!isOpen) return;

        resetForm();

        await loadPatients();
    }
);

async function loadPatients() {
    try {
        loadingPatients.value = true;

        const { data } = await axios.get(
            `/patients/options/${modal.clinicId.value}`
        );

        patientOptions.value = data ?? [];

    } catch {
        toast.error(
            'Erro ao carregar pacientes'
        );
    } finally {
        loadingPatients.value = false;
    }
}

function addPatient(patientId: number | null) {
    if (!patientId) return;

    const patient = patientOptions.value.find(
        p => p.value === patientId
    );

    if (!patient) return;

    form.patients.push(patient);

    selectedPatient.value = null;
}

function removePatient(patientId: number) {
    form.patients = form.patients.filter(
        (patient) =>
            patient.value !== patientId
    );
}

function resetForm() {
    form.patients = [];
    selectedPatient.value = null;
}

function close() {
    modal.isOpen.value = false;

    resetForm();
}

async function submit() {
    if (!form.patients.length) {
        toast.error(
            'Selecione pelo menos um paciente'
        );

        return;
    }

    try {
        loading.value = true;
        await axios.post(
            `/clinics-management/${modal.clinicId.value}/waiting-list`,
            {
                patient_ids: form.patients.map(
                    patient => patient.value
                )
            }
        );

        toast.success(
            'Pacientes adicionados à lista de espera'
        );
        refreshTableRef?.value?.();
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
            'Erro ao adicionar pacientes'
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
            class="w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl"
        >
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    Adicionar pacientes à lista de espera
                </h2>

                <p class="text-sm text-gray-500">
                    Selecione os pacientes que deseja incluir na lista.
                </p>
            </div>
            <hr />

            <div class="grid grid-cols-2 gap-6 py-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Buscar pacientes
                    </label>

                    <AppMultiselect
                        v-model="selectedPatient"
                        :options="availablePatients"
                        :loading="loadingPatients"
                        :searchable="true"
                        :can-clear="true"
                        :close-on-select="true"
                        label="label"
                        value-prop="value"
                        placeholder="Buscar paciente"
                        @select="addPatient"
                    />

                    <p
                        v-if="!availablePatients.length"
                        class="mt-2 text-sm text-gray-500"
                    >
                        Nenhum paciente disponível
                    </p>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label
                            class="text-sm font-medium text-gray-700"
                        >
                            Pacientes adicionados
                        </label>

                        <span
                            class="rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700"
                        >
                            {{ form.patients.length }}
                        </span>
                    </div>

                    <div
                        class="min-h-50 max-h-50 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3"
                    >
                        <div
                            v-for="patient in form.patients"
                            :key="patient.value"
                            class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 shadow-sm"
                        >
                            <span class="text-sm text-gray-700">
                                {{ patient.label }}
                            </span>
                            <button
                                type="button"
                                class="cursor-pointer text-red-500 hover:text-red-700"
                                @click="removePatient(patient.value)"
                            >

                                <X />
                            </button>
                        </div>

                        <div
                            v-if="!form.patients.length"
                            class="flex h-40 items-center justify-center text-sm text-gray-500"
                        >
                            Nenhum paciente selecionado
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton
                    @click="close"
                />
                <SaveButton
                    :loading="loading"
                    @click="submit"
                >
                    Adicionar pacientes
                </SaveButton>
            </div>
        </div>
    </div>
</template>