<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { StudentTabContextKey } from '@/keys/students/studentKeys';
import { studentAcademicDataEditSchema } from '@/schemas/studentAcademicDataEdit.shema';
import type { Student } from '@/types/student/student';
import { usePage } from '@inertiajs/vue3';
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
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar dados acadêmicos do aluno"
				subtitle="Atualize as informações acadêmicas do aluno."
			/>

			<form
				class="min-h-0 flex-1 px-6"
				@submit.prevent="submit"
			>
				<div class="space-y-4 py-5">
					<BaseInput
						v-model="form.registration"
						label="Registro acadêmico (*)"
						type="text"
						maxlength="255"
						placeholder="Registro acadêmico"
					/>

					<AppMultiselect
						v-model="form.period"
						:options="periodsOptions"
						field-label="Período (*)"
						label="label"
						value-prop="value"
						:searchable="true"
						:close-on-select="true"
						:can-clear="true"
						:append-to-body="true"
						placeholder="Selecione o período"
					/>
				</div>
			</form>

			<FormFooter
				:loading="loading"
				action="save"
				action-label="Salvar"
				@cancel="close"
				@save="submit"
			/>
		</div>
	</div>
</template>
