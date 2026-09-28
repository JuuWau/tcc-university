<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { PrePatientConvertKey, RefreshTableKey } from '@/keys/pre-patients/prePatientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { prePatientConvertSchema } from '@/schemas/prePatientConvert.schema';
import axios from 'axios';
import { inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const convertModal = inject<any>(PrePatientConvertKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!convertModal || !loading) {
    throw new Error(
        'PrePatientConvertModal precisa estar dentro do provider',
    );
}

const form = reactive({
    code: '',
});

const errors = reactive({
    code: '',
});

const codeInput = ref<{ focus: () => void } | null>(null);

function clearErrors() {
    errors.code = '';
}

function focusFirstError(field: string) {
    if (field === 'code') {
        codeInput.value?.focus();
    }
}

watch(
    () => convertModal.isOpen.value,
    async (isOpen) => {
        if (!isOpen || !convertModal.prePatient.value) {
            return;
        }

        clearErrors();

        try {
            const response = await axios.get('/patients/next-code', {
                params: {
                    patient_type: convertModal.prePatient.value.patient_type,
                },
            });

            form.code = response.data.code;
        } catch {
            toast.error('Não foi possível carregar o próximo código do paciente.');
        }
    },
);

function close() {
    convertModal.isOpen.value = false;
    form.code = '';
    clearErrors();
}

async function submit() {
    if (!convertModal.prePatient.value || loading.value) {
        return;
    }

    clearErrors();

    const validation = prePatientConvertSchema.safeParse(form);

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
            `/pre-patients/${convertModal.prePatient.value.id}/convert`,
            validation.data,
        );

        close();

        toast.success(response.data.message);

        setTimeout(() => {
            refreshTableRef?.value?.();
        }, 0);
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao converter pré-paciente em paciente',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="convertModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Converter em paciente"
                subtitle="Informe os dados necessários para a conversão."
            />

            <div class="px-6 py-5">
                <p class="text-sm leading-relaxed text-gray-600">
                    Tem certeza que deseja converter
                    <strong class="font-semibold text-gray-900">
                        {{ convertModal.prePatient?.value?.name }}
                    </strong>
                    em paciente?
                </p>

                <div class="mt-5">
                    <BaseInput
                        ref="codeInput"
                        v-model="form.code"
                        label="Código do paciente"
                        placeholder="Digite o código do paciente"
                        required
                        :error="errors.code"
                    />
                </div>

                <div
                    class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4"
                >
                    <p class="text-sm leading-relaxed text-blue-700">
                        Os dados do pré-paciente serão utilizados para
                        cadastrar o paciente. Após a conversão, este
                        pré-paciente não poderá mais ser editado ou excluído.
                    </p>
                </div>
            </div>

            <FormFooter
                :loading="loading"
                action="save"
                action-label="Converter"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>