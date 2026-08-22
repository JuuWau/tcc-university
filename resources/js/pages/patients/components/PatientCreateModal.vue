<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { PatientCreateKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { patientCreateSchema } from '@/schemas/patient.schema';
import type { StudentOption } from '@/types/patient/patient';
import { usePage } from '@inertiajs/vue3';
import AppMultiselect from '@/components/AppMultiselect.vue';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(PatientCreateKey);
const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const loading = inject(LoadingKey);
const page = usePage();
const viaCep = ViaCep();
const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);
const patientTypeOptions = [
    { label: 'Adulto', value: 'adulto' },
    { label: 'Pediatria', value: 'pediatria' },
];

const biologicalSexOptions = [
    { label: 'Masculino', value: 'male' },
    { label: 'Feminino', value: 'female' },
];

const students = computed(
    () => (page.props as { students?: StudentOption[] }).students ?? [],
);
const studentsOptions = computed(() =>
    students.value.map((s) => ({ label: s.name, value: s.id })),
);
const stateOptions = computed(() =>
    states.value.map((s) => ({ label: s.nome, value: s.sigla })),
);
const cityOptions = computed(() =>
    cities.value.map((c) => ({ label: c.nome, value: c.nome })),
);

if (!createModal) {
    throw new Error('PatientCreateModal precisa estar dentro do provider');
}
const modal = createModal;

const form = reactive({
    code: '' as string | null,
    name: '' as string | null,
    email: '' as string | null,
    student_ids: [] as number[],
    cpf: '' as string | null,
    phone: '' as string | null,
    birth_date: '' as string | null,
    biological_sex: null as 'male' | 'female' | null,
    cep: '' as string | null,
    street: '' as string | null,
    neighborhood: '' as string | null,
    number: '' as string | null,
    complement: null as string | null,
    city: '' as string | null,
    state: '' as string | null,
    patient_type: null as 'adulto' | 'pediatria' | null,
});

watch(
    () => modal.isOpen.value,
    (isOpen) => {
        if (isOpen) {
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
            form.city = null;
            return;
        }
        cities.value = await IbgeService.getCityData(newState);
        if (!cities.value.some((c) => c.nome === form.city)) {
            form.city = null;
        }
    },
);

function close() {
    modal.isOpen.value = false;
    form.code = '';
    form.name = '';
    form.email = null;
    form.student_ids = [];
    form.cpf = null;
    form.phone = null;
    form.birth_date = null;
    form.biological_sex = null;
    form.cep = null;
    form.street = null;
    form.neighborhood = null;
    form.number = null;
    form.complement = null;
    form.city = null;
    form.state = null;
    form.patient_type = null;
}

async function submit() {
    if (loading?.value) return;

    const validation = patientCreateSchema.safeParse(form);
    if (!validation.success) {
        toast.error(validation.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.post('/patients', validation.data);

        toast.success('Paciente cadastrado com sucesso!');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao cadastrar paciente',
        );
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">Novo Paciente</h2>
            <hr />

            <div class="space-y-4 py-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            for="patient-code"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Código (*)
                        </label>
                        <input
                            id="patient-code"
                            v-model="form.code"
                            type="text"
                            maxlength="50"
                            class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="Código do paciente"
                        />
                    </div>

                    <div>
                        <label
                            for="patient-name"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Nome completo (*)
                        </label>
                        <input
                            id="patient-name"
                            v-model="form.name"
                            type="text"
                            maxlength="255"
                            class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="Nome do paciente"
                        />
                    </div>
                </div>

                <div>
                    <label
                        for="patient-email"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Email
                    </label>

                    <input
                        id="patient-email"
                        v-model="form.email"
                        type="email"
                        maxlength="255"
                        class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        placeholder="email@exemplo.com"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            CPF
                        </label>
                        <input
                            v-model="form.cpf"
                            type="text"
                            maxlength="14"
                            v-mask="'###.###.###-##'"
                            class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="000.000.000-00"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Telefone
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            v-mask="'(##) #####-####'"
                            class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="(99) 99999-9999"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Data de nascimento
                        </label>
                        <input
                            v-model="form.birth_date"
                            type="date"
                            class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Sexo biológico (*)
                        </label>

                        <AppMultiselect
                            v-model="form.biological_sex"
                            :options="biologicalSexOptions"
                            label="label"
                            value-prop="value"
                            :searchable="false"
                            :close-on-select="true"
                            :can-clear="true"
                            :append-to-body="true"
                            placeholder="Selecione o sexo"
                        />
                    </div>
                </div>

                <div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Tipo de atendimento (*)
                        </label>

                        <AppMultiselect
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
                </div>
                <div class="grid grid-cols-1 gap-4 pb-4 md:grid-cols-1">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Estudantes
                        </label>
                        <AppMultiselect
                            v-model="form.student_ids"
                            :options="studentsOptions"
                            mode="tags"
                            label="label"
                            value-prop="value"
                            :append-to-body="true"
                            :searchable="true"
                            :close-on-select="false"
                            :can-clear="true"
                            placeholder="Escolha os estudantes"
                        />
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">
                        Endereço (opcional)
                    </h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                CEP
                            </label>
                            <input
                                v-model="form.cep"
                                type="text"
                                maxlength="9"
                                v-mask="'#####-###'"
                                class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                                placeholder="00000-000"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Rua
                            </label>
                            <input
                                v-model="form.street"
                                type="text"
                                maxlength="100"
                                class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                                placeholder="Logradouro"
                            />
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Número
                            </label>
                            <input
                                v-model="form.number"
                                type="text"
                                maxlength="10"
                                class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Bairro
                            </label>
                            <input
                                v-model="form.neighborhood"
                                type="text"
                                maxlength="50"
                                class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
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
                                maxlength="50"
                                class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Estado
                            </label>
                            <AppMultiselect
                                v-model="form.state"
                                :options="stateOptions"
                                label="label"
                                value-prop="value"
                                :append-to-body="true"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                placeholder="UF"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Cidade
                            </label>
                            <AppMultiselect
                                v-model="form.city"
                                :options="cityOptions"
                                label="label"
                                value-prop="value"
                                :append-to-body="true"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                placeholder="Cidade"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-200 pt-4">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
