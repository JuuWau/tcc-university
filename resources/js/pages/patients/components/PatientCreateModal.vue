<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { PatientCreateKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { patientCreateSchema } from '@/schemas/patient.schema';
import type { StudentOption } from '@/types/patient/patient';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(PatientCreateKey);

const refreshTableRef = inject<{ value: (() => void) | null }>(
    RefreshTableKey,
);

const loading = inject(LoadingKey);

const page = usePage();

const viaCep = ViaCep();

const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);

const students = computed(
    () => (page.props as { students?: StudentOption[] }).students ?? [],
);

const studentsOptions = computed(() =>
    students.value.map((student) => ({
        label: student.name,
        value: student.id,
    })),
);

const stateOptions = computed(() =>
    states.value.map((state) => ({
        label: state.nome,
        value: state.sigla,
    })),
);

const cityOptions = computed(() =>
    cities.value.map((city) => ({
        label: city.nome,
        value: city.nome,
    })),
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
    patient_type: null as 'adult' | 'pediatrics' | null,
});

const errors = reactive({
    code: '',
    name: '',
    email: '',
    student_ids: '',
    cpf: '',
    phone: '',
    birth_date: '',
    biological_sex: '',
    cep: '',
    street: '',
    neighborhood: '',
    number: '',
    complement: '',
    city: '',
    state: '',
    patient_type: '',
});

const codeInput = ref<{ focus: () => void } | null>(null);
const nameInput = ref<{ focus: () => void } | null>(null);
const emailInput = ref<{ focus: () => void } | null>(null);
const studentInput = ref<{ focus: () => void } | null>(null);
const cpfInput = ref<{ focus: () => void } | null>(null);
const phoneInput = ref<{ focus: () => void } | null>(null);
const birthDateInput = ref<{ focus: () => void } | null>(null);
const biologicalSexInput = ref<{ focus: () => void } | null>(null);
const patientTypeInput = ref<{ focus: () => void } | null>(null);
const cepInput = ref<{ focus: () => void } | null>(null);
const streetInput = ref<{ focus: () => void } | null>(null);
const neighborhoodInput = ref<{ focus: () => void } | null>(null);
const numberInput = ref<{ focus: () => void } | null>(null);
const complementInput = ref<{ focus: () => void } | null>(null);
const stateInput = ref<{ focus: () => void } | null>(null);
const cityInput = ref<{ focus: () => void } | null>(null);

const patientTypeOptions = [
    {
        label: 'Adulto',
        value: 'adult',
    },
    {
        label: 'Pediatria',
        value: 'pediatrics',
    },
];

const biologicalSexOptions = [
    {
        label: 'Masculino',
        value: 'male',
    },
    {
        label: 'Feminino',
        value: 'female',
    },
];

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        errors[key as keyof typeof errors] = '';
    });
}

watch(
    () => modal.isOpen.value,
    (isOpen) => {
        if (isOpen) {
            clearErrors();
            void loadStates();
        }
    },
);

async function loadStates() {
    if (states.value.length) {
        return;
    }

    states.value = await IbgeService.getUfData();
}

watch(
    () => form.cep,
    async (newCep) => {
        const cepClean = newCep?.replace(/\D/g, '');

        if (cepClean && cepClean.length === 8) {
            const data = await viaCep.getCepData(cepClean);

            if (!data) {
                return;
            }

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

        if (!cities.value.some((city) => city.nome === form.city)) {
            form.city = null;
        }
    },
);

function close() {
    clearErrors();

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
    if (loading?.value) {
        return;
    }

    clearErrors();

    const validation = patientCreateSchema.safeParse(form);

    if (!validation.success) {
        validation.error.issues.forEach((issue) => {
            const field = issue.path[0] as keyof typeof errors;

            if (field in errors) {
                errors[field] = issue.message;
            }
        });

        const firstError = validation.error.issues[0]?.path[0];

        if (firstError === 'code') {
            codeInput.value?.focus();
        } else if (firstError === 'name') {
            nameInput.value?.focus();
        } else if (firstError === 'email') {
            emailInput.value?.focus();
        } else if (firstError === 'student_ids') {
            studentInput.value?.focus();
        } else if (firstError === 'cpf') {
            cpfInput.value?.focus();
        } else if (firstError === 'phone') {
            phoneInput.value?.focus();
        } else if (firstError === 'birth_date') {
            birthDateInput.value?.focus();
        } else if (firstError === 'biological_sex') {
            biologicalSexInput.value?.focus();
        } else if (firstError === 'patient_type') {
            patientTypeInput.value?.focus();
        } else if (firstError === 'cep') {
            cepInput.value?.focus();
        } else if (firstError === 'street') {
            streetInput.value?.focus();
        } else if (firstError === 'neighborhood') {
            neighborhoodInput.value?.focus();
        } else if (firstError === 'number') {
            numberInput.value?.focus();
        } else if (firstError === 'complement') {
            complementInput.value?.focus();
        } else if (firstError === 'state') {
            stateInput.value?.focus();
        } else if (firstError === 'city') {
            cityInput.value?.focus();
        }

        return;
    }

    try {
        if (loading) {
            loading.value = true;
        }

        await axios.post('/patients', validation.data);

        toast.success('Paciente cadastrado com sucesso!');

        close();

        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao cadastrar paciente',
        );
    } finally {
        if (loading) {
            loading.value = false;
        }
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white"
        >
            <FormHeader
                title="Novo Paciente"
                subtitle="Preencha os dados para cadastrar um novo paciente."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <div class="space-y-4 py-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <BaseInput
                            ref="codeInput"
                            v-model="form.code"
                            label="Código"
                            type="text"
                            maxlength="50"
                            placeholder="Código do paciente"
                            required
                            :error="errors.code"
                        />

                        <BaseInput
                            ref="nameInput"
                            v-model="form.name"
                            label="Nome completo"
                            type="text"
                            maxlength="255"
                            placeholder="Nome do paciente"
                            required
                            :error="errors.name"
                        />
                    </div>

                    <div>
                        <BaseInput
                            ref="emailInput"
                            v-model="form.email"
                            label="Email"
                            type="email"
                            maxlength="255"
                            placeholder="email@exemplo.com"
                            :error="errors.email"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <BaseInput
                            ref="cpfInput"
                            v-model="form.cpf"
                            label="CPF"
                            type="text"
                            maxlength="14"
                            v-mask="'###.###.###-##'"
                            placeholder="000.000.000-00"
                            :error="errors.cpf"
                        />

                        <BaseInput
                            ref="phoneInput"
                            v-model="form.phone"
                            label="Telefone"
                            type="tel"
                            v-mask="'(##) #####-####'"
                            placeholder="(99) 99999-9999"
                            :error="errors.phone"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <BaseInput
                            ref="birthDateInput"
                            v-model="form.birth_date"
                            label="Data de nascimento"
                            type="date"
                            :error="errors.birth_date"
                        />

                        <AppMultiselect
                            ref="biologicalSexInput"
                            v-model="form.biological_sex"
                            :options="biologicalSexOptions"
                            field-label="Sexo biológico"
                            value-prop="value"
                            :searchable="false"
                            :close-on-select="true"
                            :can-clear="true"
                            :append-to-body="true"
                            placeholder="Selecione o sexo"
                            :error="errors.biological_sex"
                        />
                    </div>

                    <div>
                        <AppMultiselect
                            ref="patientTypeInput"
                            v-model="form.patient_type"
                            :options="patientTypeOptions"
                            field-label="Tipo de paciente"
                            value-prop="value"
                            :searchable="false"
                            :close-on-select="true"
                            :can-clear="false"
                            :append-to-body="true"
                            placeholder="Selecione o tipo"
                            :error="errors.patient_type"
                        />
                    </div>

                    <div class="pb-4">
                        <AppMultiselect
                            ref="studentInput"
                            v-model="form.student_ids"
                            :options="studentsOptions"
                            mode="tags"
                            field-label="Estudantes"
                            value-prop="value"
                            :append-to-body="true"
                            :searchable="true"
                            :close-on-select="false"
                            :can-clear="true"
                            placeholder="Escolha os estudantes"
                            :error="errors.student_ids"
                        />
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="mb-3 text-sm font-semibold text-gray-700">
                            Endereço (opcional)
                        </h3>

                        <div
                            class="grid grid-cols-1 gap-4 md:grid-cols-3"
                        >
                            <BaseInput
                                ref="cepInput"
                                v-model="form.cep"
                                label="CEP"
                                type="text"
                                maxlength="9"
                                v-mask="'#####-###'"
                                placeholder="00000-000"
                                :error="errors.cep"
                            />

                            <div class="md:col-span-2">
                                <BaseInput
                                    ref="streetInput"
                                    v-model="form.street"
                                    label="Rua"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Logradouro"
                                    :error="errors.street"
                                />
                            </div>
                        </div>

                        <div
                            class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3"
                        >
                            <BaseInput
                                ref="numberInput"
                                v-model="form.number"
                                label="Número"
                                type="text"
                                maxlength="10"
                                placeholder="Número"
                                :error="errors.number"
                            />

                            <BaseInput
                                ref="neighborhoodInput"
                                v-model="form.neighborhood"
                                label="Bairro"
                                type="text"
                                maxlength="50"
                                placeholder="Bairro"
                                :error="errors.neighborhood"
                            />

                            <BaseInput
                                ref="complementInput"
                                v-model="form.complement"
                                label="Complemento"
                                type="text"
                                maxlength="50"
                                placeholder="Complemento"
                                :error="errors.complement"
                            />
                        </div>

                        <div
                            class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2"
                        >
                            <AppMultiselect
                                ref="stateInput"
                                v-model="form.state"
                                :options="stateOptions"
                                field-label="Estado"
                                value-prop="value"
                                :append-to-body="true"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                placeholder="Selecione o Estado"
                                :error="errors.state"
                            />

                            <AppMultiselect
                                ref="cityInput"
                                v-model="form.city"
                                :options="cityOptions"
                                field-label="Cidade"
                                value-prop="value"
                                :append-to-body="true"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                placeholder="Selecione a Cidade"
                                :error="errors.city"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <FormFooter
                :loading="loading"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>