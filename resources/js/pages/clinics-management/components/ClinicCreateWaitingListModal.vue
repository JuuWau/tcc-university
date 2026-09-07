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
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

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
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg bg-white shadow-xl"
		>
			<FormHeader
				title="Adicionar pacientes à lista de espera"
				subtitle="Selecione os pacientes que deseja incluir na lista."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="py-5">
					<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
						<div>
							<AppMultiselect
								v-model="selectedPatient"
								:options="availablePatients"
								:loading="loadingPatients"
								field-label="Buscar pacientes"
								label="label"
								value-prop="value"
								:searchable="true"
								:can-clear="true"
								:close-on-select="true"
								:append-to-body="true"
								placeholder="Buscar paciente"
								@select="addPatient"
							/>

							<p
								v-if="!availablePatients.length"
								class="mt-2 text-sm text-gray-500"
							>
								Nenhum paciente disponível.
							</p>
						</div>

						<div>
							<div class="mb-2 flex items-center justify-between">
								<label class="block text-sm font-medium text-gray-700">
									Pacientes adicionados
								</label>

								<span
									class="inline-flex min-w-6 items-center justify-center rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700"
								>
									{{ form.patients.length }}
								</span>
							</div>

							<div
								class="min-h-50 max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3"
							>
								<div
									v-for="patient in form.patients"
									:key="patient.value"
									class="mb-2 flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2.5 shadow-sm last:mb-0"
								>
									<span
										class="min-w-0 truncate pr-3 text-sm text-gray-700"
									>
										{{ patient.label }}
									</span>

									<button
										type="button"
										class="flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-600"
										@click="removePatient(patient.value)"
									>
										<X class="h-4 w-4" />
									</button>
								</div>

								<div
									v-if="!form.patients.length"
									class="flex h-40 items-center justify-center text-center text-sm text-gray-500"
								>
									Nenhum paciente selecionado.
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="save"
				action-label="Adicionar pacientes"
				@cancel="close"
				@save="submit"
			/>
		</div>
	</div>
</template>