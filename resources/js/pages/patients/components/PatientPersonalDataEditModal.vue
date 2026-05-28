<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import { patientPersonalDataEditSchema } from '@/schemas/patientPersonalDataEdit.schema';
import type { PatientForTab } from '@/types/patient/patient';
import Multiselect from '@vueform/multiselect';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const context = inject(PatientTabContextKey);
if (!context) {
    throw new Error(
        'PatientPersonalDataEditModal must be used inside PatientTab',
    );
}

const patient = computed(() => context.patient.value);
const editPersonalDataModalOpen = context.editPersonalDataModalOpen;

const patientTypeOptions = [
    { label: 'Adulto', value: 'adulto' },
    { label: 'Pediatria', value: 'pediatria' },
];

const emit = defineEmits<{
    updated: [];
}>();

const loading = ref(false);
const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);
const viaCep = ViaCep();

const stateOptions = computed(() =>
    states.value.map((s) => ({ label: s.nome, value: s.sigla })),
);

const cityOptions = computed(() =>
    cities.value.map((c) => ({ label: c.nome, value: c.nome })),
);

const form = reactive({
    name: '',
    email: '',
    phone: '',
    cpf: '',
    birth_date: '',
    patient_type: null as 'adulto' | 'pediatria' | null,
    cep: '',
    street: '',
    neighborhood: '',
    number: '',
    complement: null as string | null,
    city: '',
    state: '',
});

function formatDateForInput(dateStr: string | null | undefined): string {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return '';
    return d.toISOString().slice(0, 10);
}

function populateForm() {
    const p = patient.value;
    const addr = p?.address;

    form.name = p?.name ?? '';
    form.email = p?.email ?? '';
    form.phone = p?.phone ?? '';
    form.cpf = p?.cpf ?? '';
    form.birth_date = formatDateForInput(p?.birth_date);
    form.cep = addr?.cep ?? '';
    form.street = addr?.street ?? '';
    form.neighborhood = addr?.neighborhood ?? '';
    form.patient_type = p?.patient_type ?? null;
    form.number = addr?.number ?? '';
    form.complement = addr?.complement ?? null;
    form.city = addr?.city ?? '';
    form.state = addr?.state ?? '';
}

watch(
    () => editPersonalDataModalOpen.value,
    (isOpen) => {
        if (isOpen) {
            populateForm();
            void loadStates();
        }
    },
);

async function loadStates() {
    if (states.value.length) return;
    states.value = await IbgeService.getUfData();
}

watch(
    () => form.cep,
    async (newCep) => {
        const cepClean = newCep?.replace(/\D/g, '');
        if (cepClean && cepClean.length === 8) {
            const data = await viaCep.getCepData(cepClean);
            if (!data) return;
            form.street = data.logradouro ?? '';
            form.neighborhood = data.bairro ?? '';
            form.city = data.localidade ?? '';
            form.state = data.uf ?? '';
            if (data.uf) {
                cities.value = await IbgeService.getCityData(data.uf);
            }
        }
    },
);

watch(
    () => form.state,
    async (newState) => {
        if (!newState) {
            cities.value = [];
            form.city = '';
            return;
        }
        cities.value = await IbgeService.getCityData(newState);
        if (!cities.value.some((c) => c.nome === form.city)) {
            form.city = '';
        }
    },
);

function close() {
    editPersonalDataModalOpen.value = false;
}

async function submit() {
    if (loading.value || !patient.value) return;

    const result = patientPersonalDataEditSchema.safeParse({
        name: form.name,
        email: form.email,
        phone: form.phone,
        cpf: form.cpf,
        birth_date: form.birth_date,
        patient_type: form.patient_type,
        cep: form.cep,
        street: form.street,
        neighborhood: form.neighborhood,
        number: form.number,
        complement: form.complement,
        city: form.city,
        state: form.state,
    });
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const { data } = await axios.patch<{
            message: string;
            patient: PatientForTab;
        }>(`/patients/${patient.value.id}`, result.data);
        toast.success(data.message ?? 'Dados atualizados com sucesso');
        emit('updated');
        close();
    } catch (err: any) {
        toast.error(
            err.response?.data?.message ??
                'Erro ao atualizar dados do paciente',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="editPersonalDataModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="max-h-[90vh] w-full max-w-2xl rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">Editar dados do paciente</h2>
            <hr />

            <form class="space-y-4 pt-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Nome completo (*)
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            maxlength="255"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="Nome completo"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            E-mail
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="email@exemplo.com"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Telefone
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            v-mask="'(##) #####-####'"
                            placeholder="(99) 99999-9999"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            CPF
                        </label>
                        <input
                            v-model="form.cpf"
                            type="text"
                            maxlength="14"
                            v-mask="'###.###.###-##'"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Data de nascimento
                    </label>
                    <input
                        v-model="form.birth_date"
                        type="date"
                        class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Tipo de atendimento (*)
                    </label>

                    <Multiselect
                        v-model="form.patient_type"
                        :options="patientTypeOptions"
                        label="label"
                        value-prop="value"
                        :searchable="false"
                        :close-on-select="true"
                        :can-clear="false"
                        :append-to-body="true"
                        placeholder="Selecione o tipo"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            CEP (*)
                        </label>
                        <input
                            v-model="form.cep"
                            type="text"
                            v-mask="'#####-###'"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Endereço (*)
                        </label>
                        <input
                            v-model="form.street"
                            type="text"
                            maxlength="100"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Bairro (*)
                        </label>
                        <input
                            v-model="form.neighborhood"
                            type="text"
                            maxlength="50"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Número (*)
                        </label>
                        <input
                            v-model="form.number"
                            type="text"
                            maxlength="5"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Complemento
                        </label>
                        <input
                            v-model="form.complement"
                            type="text"
                            maxlength="20"
                            class="w-full rounded border px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Estado (*)
                        </label>
                        <Multiselect
                            v-model="form.state"
                            :options="stateOptions"
                            label="label"
                            value-prop="value"
                            :searchable="true"
                            :close-on-select="true"
                            :can-clear="true"
                            placeholder="Selecione o estado"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Cidade (*)
                        </label>
                        <Multiselect
                            v-model="form.city"
                            :options="cityOptions"
                            label="label"
                            value-prop="value"
                            :searchable="true"
                            :close-on-select="true"
                            :can-clear="true"
                            placeholder="Selecione a cidade"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <CancelButton @click="close" />
                    <SaveButton :loading="loading" @click.stop="submit" />
                </div>
            </form>
        </div>
    </div>
</template>
