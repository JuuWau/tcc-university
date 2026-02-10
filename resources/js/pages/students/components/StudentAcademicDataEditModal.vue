<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { StudentTabContextKey } from '@/keys/students/studentKeys';
import { studentAcademicDataEditSchema } from '@/schemas/studentAcademicDataEdit.shema';
import type { Student } from '@/types/student/student';
import { usePage } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const context = inject(StudentTabContextKey);
if (!context) {
    throw new Error('StudentsEditModal must be used inside StudentTab');
}

const page = usePage();
const student = computed(() => context.student.value);
const academicDataEditModalOpen = context.academicDataEditModalOpen;

const periodsOptions = computed(() => {
    const periods =
        (
            page.props as {
                periods?: Array<{
                    id: number;
                    academic_year: number;
                    semester: number;
                    calendar_year: number;
                }>;
            }
        ).periods ?? [];
    return periods.map((p) => ({
        label: `${p.academic_year}º ano ${p.semester}º semestre ${p.calendar_year}`,
        value: p.id,
    }));
});

const emit = defineEmits<{
    updated: [];
}>();

const loading = ref(false);

const form = reactive({
    registration: '' as string,
    period: null as number | null,
});

watch(
    () => academicDataEditModalOpen.value,
    (isOpen) => {
        if (isOpen && student.value) {
            form.registration = student.value.registration ?? '';
            const currentPeriod =
                (
                    student.value.periods as Array<{
                        id: number;
                        pivot?: { is_current?: boolean };
                    }>
                )?.find((p) => p.pivot?.is_current) ??
                student.value.periods?.[0];
            form.period = currentPeriod?.id ?? null;
        }
    },
);

function close() {
    academicDataEditModalOpen.value = false;
}

async function submit() {
    if (loading.value) return;

    const payload: Record<string, unknown> = {
        registration: form.registration,
        period: form.period ?? undefined,
    };

    const result = studentAcademicDataEditSchema.safeParse(payload);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const { data } = await axios.patch<{
            message: string;
            student: Student;
        }>(`/students/${student.value.id}/academic-data`, payload);
        toast.success(data.message ?? 'Dados atualizados com sucesso');
        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err && typeof err === 'object' && 'response' in err
                ? (err as { response?: { data?: { message?: string } } })
                      .response?.data?.message
                : null;
        toast.error(message ?? 'Erro ao atualizar dados do aluno');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="academicDataEditModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">
                Editar dados acadêmicos do aluno
            </h2>
            <hr />

            <form class="space-y-4 pt-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Registro Acadêmico (*)
                        </label>
                        <input
                            v-model="form.registration"
                            type="text"
                            maxlength="255"
                            class="w-full rounded border px-3 py-2 text-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                            placeholder="Registro Acadêmico"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Período (*)
                        </label>
                        <Multiselect
                            v-model="form.period"
                            :options="periodsOptions"
                            label="label"
                            value-prop="value"
                            :searchable="true"
                            :close-on-select="true"
                            :can-clear="false"
                            placeholder="Selecione o período"
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
