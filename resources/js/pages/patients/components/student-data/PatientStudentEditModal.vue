```vue
<script setup lang="ts">
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import type { PatientForTab } from '@/types/patient/patient';
import axios from 'axios';
import { computed, inject, ref, unref } from 'vue';
import { toast } from 'vue3-toastify';

import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import PatientStudentEditForm from '@/components/form/patient/PatientStudentEditForm.vue';

const context = inject(PatientTabContextKey);
const formRef = ref<InstanceType<typeof PatientStudentEditForm> | null>(null);

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

function close() {
    editStudentModalOpen.value = false;
}

async function submit(data: {
    code: string;
    student_ids: number[];
    status: string;
}) {
    if (loading.value || !patient.value) return;

    try {
        loading.value = true;

        const id = patient.value.id;

        await axios.patch<{ message: string; patient: PatientForTab }>(
            `/patients/${id}/student-data`,
            data,
        );

        toast.success('Dados atualizados com sucesso');

        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err && typeof err === 'object' && 'response' in err
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
                <PatientStudentEditForm
                    ref="formRef"
                    :patient="patient"
                    :students="studentsList"
                    @submit="submit"
                />
            </div>

            <FormFooter
                :loading="loading"
                @cancel="close"
                @save="formRef?.submit()"
            />
        </div>
    </div>
</template>
