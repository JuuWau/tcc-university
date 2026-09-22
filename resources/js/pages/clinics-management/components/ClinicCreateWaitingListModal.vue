<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import { ClinicCreateWaitingListKey, RefreshTableKey } from '@/keys/clinics-management/clinicManagementShowKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import { X } from 'lucide-vue-next';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';

type PrePatientOption = {
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
    prePatients: [] as PrePatientOption[],
});

const selectedPrePatient = ref<PrePatientOption | null>(null);
const prePatientOptions = ref<PrePatientOption[]>([]);
const loadingPatients = ref(false);

const availablePrePatients = computed(() => {
    return prePatientOptions.value.filter(
        (prePatient) =>
            !form.prePatients.some(
                (selected) => selected.value === prePatient.value
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
            `/pre-patients/options/${modal.clinicId.value}`
        );

        prePatientOptions.value = data ?? [];

    } catch {
        toast.error(
            'Erro ao carregar pacientes'
        );
    } finally {
        loadingPatients.value = false;
    }
}

function addPrePatient(prePatientId: number | null) {
	if (!prePatientId) return;

	const patient = prePatientOptions.value.find(
		p => p.value === prePatientId,
	);

	if (!patient) return;

	form.prePatients.push(patient);
	selectedPrePatient.value = null;
}

function removePrePatient(prePatientId: number) {
    form.prePatients = form.prePatients.filter(
        (patient) =>
            patient.value !== prePatientId
    );
}

function resetForm() {
    form.prePatients = [];
    selectedPrePatient.value = null;
}

function close() {
    modal.isOpen.value = false;

    resetForm();
}

async function submit() {
    if (!form.prePatients.length) {
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
                pre_patient_ids: form.prePatients.map(
                    pre_patient => pre_patient.value
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
				title="Adicionar pré-pacientes à lista de espera"
				subtitle="Selecione os pré-pacientes que deseja incluir na lista."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="py-5">
					<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
						<div>
							<AppMultiselect
								v-model="selectedPrePatient"
								:options="availablePrePatients"
								:loading="loadingPatients"
								field-label="Buscar pré-pacientes"
								label="label"
								value-prop="value"
								:searchable="true"
								:can-clear="true"
								:close-on-select="true"
								:append-to-body="true"
								placeholder="Buscar paciente"
								@select="addPrePatient"
							/>

							<p
								v-if="!availablePrePatients.length"
								class="mt-2 text-sm text-gray-500"
							>
								Nenhum pré-paciente disponível.
							</p>
						</div>

						<div>
							<div class="mb-2 flex items-center justify-between">
								<label class="block text-sm font-medium text-gray-700">
									Pré-pacientes adicionados
								</label>

								<span
									class="inline-flex min-w-6 items-center justify-center rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700"
								>
									{{ form.prePatients.length }}
								</span>
							</div>

							<div
								class="min-h-50 max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3"
							>
								<div
									v-for="prePatient in form.prePatients"
									:key="prePatient.value"
									class="mb-2 flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2.5 shadow-sm last:mb-0"
								>
									<span
										class="min-w-0 truncate pr-3 text-sm text-gray-700"
									>
										{{ prePatient.label }}
									</span>

									<button
										type="button"
										class="flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-600"
										@click="removePrePatient(prePatient.value)"
									>
										<X class="h-4 w-4" />
									</button>
								</div>

								<div
									v-if="!form.prePatients.length"
									class="flex h-40 items-center justify-center text-center text-sm text-gray-500"
								>
									Nenhum pré-paciente selecionado.
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