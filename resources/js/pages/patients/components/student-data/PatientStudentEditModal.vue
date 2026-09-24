<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import { patientStudentEditSchema } from '@/schemas/patientStudentEdit.schema';
import { PATIENT_STATUS, type PatientForTab, type PatientStatusKey } from '@/types/patient/patient';
import axios from 'axios';
import { computed, inject, reactive, ref, unref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const context = inject(PatientTabContextKey);

if (!context) {
    throw new Error('PatientStudentEditModal must be used inside PatientTab');
}

const patient = computed(() => context.patient.value);
const editStudentModalOpen = context.editStudentModalOpen;
const studentsList = computed(() => unref(context.students) ?? []);

const emit = defineEmits<{
    updated: [];
}>();

const loading = ref(false);

const form = reactive({
    code: '',
    student_ids: [] as number[],
    status: 'ativo' as PatientStatusKey,
});

const errors = reactive({
    code: '',
    student_ids: '',
    status: '',
});

const codeInput = ref<{ focus: () => void } | null>(null);
const studentInput = ref<{ focus: () => void } | null>(null);
const statusInput = ref<{ focus: () => void } | null>(null);

const studentOptions = computed(() =>
    studentsList.value.map((student) => ({
        label: student.name,
        value: student.id,
    })),
);

const statusOptions = computed(() =>
    Object.entries(PATIENT_STATUS).map(([value, label]) => ({
        value,
        label,
    })),
);

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        errors[key as keyof typeof errors] = '';
    });
}

watch(
    () => patient.value,
    (patientData) => {
        if (!patientData) {
            return;
        }

        form.code = patientData.code ?? '';
        form.student_ids = patientData.student_ids ?? [];
        form.status = (patientData.status ?? 'ativo') as PatientStatusKey;

        clearErrors();
    },
    { immediate: true },
);

watch(
    () => editStudentModalOpen.value,
    (isOpen) => {
        if (isOpen) {
            clearErrors();
        }
    },
);

function close() {
    clearErrors();
    editStudentModalOpen.value = false;
}

function focusFirstError(field: string) {
    if (field === 'code') {
        codeInput.value?.focus();
    } else if (field === 'student_ids') {
        studentInput.value?.focus();
    } else if (field === 'status') {
        statusInput.value?.focus();
    }
}

async function submit() {
    if (loading.value || !patient.value) {
        return;
    }

    clearErrors();

    const result = patientStudentEditSchema.safeParse({
        code: form.code,
        student_ids: form.student_ids,
        status: form.status,
    });

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0] as keyof typeof errors;

            if (field in errors) {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0]?.path[0];

        if (firstError) {
            focusFirstError(firstError as string);
        }

        return;
    }

    try {
        loading.value = true;

        const id = patient.value.id;

        await axios.patch<{ message: string; patient: PatientForTab }>(
            `/patients/${id}/student-data`,
            result.data,
        );

        toast.success('Dados atualizados com sucesso');

        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err &&
            typeof err === 'object' &&
            'response' in err
                ? (
                      err as {
                          response?: {
                              data?: {
                                  message?: string;
                              };
                          };
                      }
                  ).response?.data?.message
                : null;

        toast.error(message ?? 'Erro ao atualizar');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="editStudentModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white"
        >
            <FormHeader
                title="Editar paciente"
                subtitle="Atualize os dados do paciente."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <form
                    class="space-y-4 py-4"
                    @submit.prevent="submit"
                >
                    <BaseInput
                        ref="codeInput"
                        v-model="form.code"
                        label="Código do paciente"
                        type="text"
                        maxlength="20"
                        placeholder="Código do paciente"
                        required
                        :error="errors.code"
                    />

                    <AppMultiselect
                        ref="studentInput"
                        v-model="form.student_ids"
                        :options="studentOptions"
                        mode="tags"
                        field-label="Estudantes"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="false"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Selecione o estudante (opcional)"
                        :error="errors.student_ids"
                    />

                    <AppMultiselect
                        ref="statusInput"
                        v-model="form.status"
                        :options="statusOptions"
                        field-label="Status"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        :append-to-body="true"
                        placeholder="Selecione o status"
                        :error="errors.status"
                    />
                </form>
            </div>

            <FormFooter
                :loading="loading"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>