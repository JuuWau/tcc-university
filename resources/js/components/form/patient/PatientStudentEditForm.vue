<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { patientStudentEditSchema } from '@/schemas/patientStudentEdit.schema';
import {
        PATIENT_STATUS,
        type PatientForTab,
        type PatientStatusKey,
        type StudentOption,
} from '@/types/patient/patient';
import { computed, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
        patient: PatientForTab | null;
        students: StudentOption[];
}>();

const emit = defineEmits<{
        submit: [
                data: { code: string; student_ids: number[]; status: PatientStatusKey },
        ];
}>();

const form = reactive({
        code: '',
        student_ids: [] as number[],
        status: 'ativo' as PatientStatusKey,
});

const studentOptions = computed(() =>
        props.students.map((student) => ({
                label: student.name,
                value: student.id,
        })),
);

const statusOptions = computed(() =>
        Object.entries(PATIENT_STATUS).map(([value, label]) => ({ value, label })),
);

watch(
        () => props.patient,
        (patient) => {
                if (!patient) return;
                form.code = patient.code ?? '';
                form.student_ids = patient.student_ids ?? [];
                form.status = (patient.status ?? 'ativo') as PatientStatusKey;
        },
        { immediate: true },
);

function submit() {
        const result = patientStudentEditSchema.safeParse({
                code: form.code,
                student_ids: form.student_ids,
                status: form.status,
        });
        if (!result.success) {
                toast.error(result.error.issues[0].message);
                return;
        }
        emit('submit', result.data);
}

defineExpose({ submit });

</script>
<template>
        <form class="space-y-4 py-4" @submit.prevent="submit">
                <BaseInput
                        v-model="form.code"
                        label="Código do paciente (*)"
                        type="text"
                        maxlength="20"
                        placeholder="Código do paciente"
                />
                <AppMultiselect
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
                />
                <AppMultiselect
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
                />
        </form>
</template>
