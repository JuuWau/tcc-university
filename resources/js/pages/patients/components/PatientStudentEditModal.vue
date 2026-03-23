<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import { patientStudentEditSchema } from '@/schemas/patientStudentEdit.schema';
import type { PatientForTab, PatientStatusKey } from '@/types/patient/patient';
import { PATIENT_STATUS } from '@/types/patient/patient';
import Multiselect from '@vueform/multiselect';
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

const studentOptions = computed(() =>
    studentsList.value.map((s) => ({ label: s.name, value: s.id })),
);

const form = reactive({
    student_id: null as number | null,
    status: 'ativo' as PatientStatusKey,
});

const statusOptions = computed(() =>
    Object.entries(PATIENT_STATUS).map(([value, label]) => ({ value, label })),
);

watch(
    () => editStudentModalOpen.value,
    (isOpen) => {
        if (isOpen && patient.value) {
            form.student_id =
                patient.value.student_id ?? patient.value.student?.id ?? null;
            form.status = (patient.value.status ?? 'ativo') as PatientStatusKey;
        }
    },
);

function close() {
    editStudentModalOpen.value = false;
}

async function submit() {
    if (loading.value) return;

    const result = patientStudentEditSchema.safeParse({
        student_id: form.student_id,
        status: form.status,
    });
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const id = patient.value!.id;
        await axios.patch<{ message: string; patient: PatientForTab }>(
            `/patients/${id}/student-data`,
            {
                student_id: result.data.student_id,
                status: result.data.status,
            },
        );
        toast.success('Estudante e status atualizados com sucesso');
        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err && typeof err === 'object' && 'response' in err
                ? (err as { response?: { data?: { message?: string } } })
                      .response?.data?.message
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
            class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">Editar estudante do paciente</h2>
            <hr />

            <form class="space-y-4 pt-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Estudante
                    </label>
                    <Multiselect
                        v-model="form.student_id"
                        :options="studentOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Selecione o estudante (opcional)"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Status
                    </label>
                    <Multiselect
                        v-model="form.status"
                        :options="statusOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        :append-to-body="true"
                        placeholder="Selecione o status"
                    />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <CancelButton @click="close" />
                    <SaveButton :loading="loading" @click.stop="submit" />
                </div>
            </form>
        </div>
    </div>
</template>
