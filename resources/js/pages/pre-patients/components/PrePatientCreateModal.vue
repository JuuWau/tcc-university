<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { PrePatientCreateKey, RefreshTableKey } from '@/keys/pre-patients/prePatientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { prePatientCreateSchema } from '@/schemas/prePatient.schema';
import axios from 'axios';
import { inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(PrePatientCreateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!createModal || !loading) {
    throw new Error('PrePatientCreateModal precisa estar dentro do provider');
}

const modal = createModal;

const form = reactive({
    name: '',
    cpf: null as string | null,
    birth_date: null as string | null,
    biological_sex: null as 'male' | 'female' | null,
    phone: null as string | null,
    email: null as string | null,
    patient_type: null as 'adult' | 'pediatric' | null,
});

const errors = reactive({
    name: '',
    cpf: '',
    birth_date: '',
    biological_sex: '',
    phone: '',
    email: '',
    patient_type: '',
});

const nameInput = ref<{ focus: () => void } | null>(null);
const cpfInput = ref<{ focus: () => void } | null>(null);
const birthDateInput = ref<{ focus: () => void } | null>(null);
const biologicalSexInput = ref<{ focus: () => void } | null>(null);
const phoneInput = ref<{ focus: () => void } | null>(null);
const emailInput = ref<{ focus: () => void } | null>(null);
const patientTypeInput = ref<{ focus: () => void } | null>(null);

const biologicalSexOptions = [
    { label: 'Feminino', value: 'female' },
    { label: 'Masculino', value: 'male' },
];

const patientTypeOptions = [
    { label: 'Adulto', value: 'adult' },
    { label: 'Pediatria', value: 'pediatric' },
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
        }
    },
);

function close() {
    clearErrors();

    modal.isOpen.value = false;

    form.name = '';
    form.cpf = null;
    form.birth_date = null;
    form.biological_sex = null;
    form.phone = null;
    form.email = null;
    form.patient_type = null;
}

function focusFirstError(field: string) {
    if (field === 'name') {
        nameInput.value?.focus();
    } else if (field === 'cpf') {
        cpfInput.value?.focus();
    } else if (field === 'birth_date') {
        birthDateInput.value?.focus();
    } else if (field === 'biological_sex') {
        biologicalSexInput.value?.focus();
    } else if (field === 'phone') {
        phoneInput.value?.focus();
    } else if (field === 'email') {
        emailInput.value?.focus();
    } else if (field === 'patient_type') {
        patientTypeInput.value?.focus();
    }
}

async function submit() {
    if (loading.value) {
        return;
    }

    clearErrors();

    const validation = prePatientCreateSchema.safeParse(form);

    if (!validation.success) {
        validation.error.issues.forEach((issue) => {
            const field = issue.path[0] as keyof typeof errors;

            if (field in errors) {
                errors[field] = issue.message;
            }
        });

        const firstError = validation.error.issues[0]?.path[0];

        if (firstError) {
            focusFirstError(firstError as string);
        }

        return;
    }

    try {
        loading.value = true;

        const response = await axios.post(
            '/pre-patients',
            validation.data,
        );

        toast.success(response.data.message);

        close();

        setTimeout(() => {
            refreshTableRef?.value?.();
        }, 0);
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao cadastrar pré-paciente',
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
            class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white"
        >
            <FormHeader
                title="Novo Pré-Paciente"
                subtitle="Preencha os dados para cadastrar um novo pré-paciente."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <div class="grid grid-cols-1 gap-4 py-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <BaseInput
                            ref="nameInput"
                            v-model="form.name"
                            label="Nome"
                            placeholder="Digite o nome completo"
                            required
                            :error="errors.name"
                        />
                    </div>

                    <BaseInput
                        ref="cpfInput"
                        v-model="form.cpf"
                        label="CPF"
                        placeholder="000.000.000-00"
                        v-mask="'###.###.###-##'"
                        :error="errors.cpf"
                    />

                    <BaseInput
                        ref="birthDateInput"
                        v-model="form.birth_date"
                        label="Data de nascimento"
                        type="date"
                        :error="errors.birth_date"
                    />

                    <BaseInput
                        ref="phoneInput"
                        v-model="form.phone"
                        label="Telefone"
                        v-mask="'(##) #####-####'"
                        placeholder="(00) 00000-0000"
                        :error="errors.phone"
                    />

                    <BaseInput
                        ref="emailInput"
                        v-model="form.email"
                        label="E-mail"
                        type="email"
                        placeholder="email@exemplo.com"
                        :error="errors.email"
                    />

                    <AppMultiselect
                        ref="biologicalSexInput"
                        v-model="form.biological_sex"
                        field-label="Sexo biológico"
                        :options="biologicalSexOptions"
                        label="label"
                        value-prop="value"
                        placeholder="Selecione"
                        :can-clear="false"
                        required
                        :append-to-body="true"
                        :error="errors.biological_sex"
                    />

                    <AppMultiselect
                        ref="patientTypeInput"
                        v-model="form.patient_type"
                        field-label="Tipo de paciente"
                        :options="patientTypeOptions"
                        label="label"
                        value-prop="value"
                        placeholder="Selecione"
                        :can-clear="false"
                        required
                        :append-to-body="true"
                        :error="errors.patient_type"
                    />
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