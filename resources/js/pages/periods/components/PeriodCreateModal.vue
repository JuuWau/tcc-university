<script setup lang="ts">
import { PeriodCreateKey, RefreshTableKey } from '@/keys/periods/periodKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { periodSchema } from '@/schemas/period.schema';
import { usePage } from '@inertiajs/vue3';
import AppMultiselect from '@/components/AppMultiselect.vue';
import axios from 'axios';
import { inject, onMounted, reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';
import FormFooter from '@/components/form/FormFooter.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormHeader from '@/components/form/FormHeader.vue';

const createModal = inject<any>(PeriodCreateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);
const specialtiesOptions = ref<{ label: string; value: number }[]>([]);

const academicYearInput = ref<{ focus: () => void } | null>(null);
const semesterInput = ref<{ focus: () => void } | null>(null);
const calendarYearInput = ref<{ focus: () => void } | null>(null);

const page = usePage();

onMounted(() => {
    const specialties = page.props.specialties as Array<{
        id: number;
        name: string;
    }>;

    specialtiesOptions.value = specialties.map((s) => ({
        label: s.name,
        value: s.id,
    }));
});

if (!createModal) {
    throw new Error('PeriodCreateModal precisa estar dentro do provider');
}

const form = reactive({
    academic_year: '' as string | null,
    semester: '' as string | null,
    calendar_year: '' as string | null,
    specialties: [] as number[],
});

const errors = reactive({
    academic_year: '',
    semester: '',
    calendar_year: '',
    specialties: '',
});

function clearErrors() {
    errors.academic_year = '';
    errors.semester = '';
    errors.calendar_year = '';
    errors.specialties = '';
}

function close() {
    createModal.isOpen.value = false;

    form.academic_year = null;
    form.semester = null;
    form.calendar_year = null;
    form.specialties = [];

    clearErrors();
}

async function submit() {
    if (loading.value) return;

    clearErrors();

    const result = periodSchema.safeParse(form);

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0];

            if (
                field === 'academic_year' ||
                field === 'semester' ||
                field === 'calendar_year' ||
                field === 'specialties'
            ) {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0];

        if (firstError.path[0] === 'academic_year') {
            academicYearInput.value?.focus();
        } else if (firstError.path[0] === 'semester') {
            semesterInput.value?.focus();
        } else if (firstError.path[0] === 'calendar_year') {
            calendarYearInput.value?.focus();
        }

        return;
    }

    try {
        loading.value = true;

	await axios.post('/periods', {
            academic_year: form.academic_year,
            semester: form.semester,
            calendar_year: form.calendar_year,
            specialties: form.specialties,
        });

	refreshTableRef?.value?.();
        toast.success('Período criado com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao criar período');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="createModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Novo período"
				subtitle="Cadastre o ano, semestre e especialidades do período."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-4 py-5">
					<BaseInput
						ref="academicYearInput"
						id="academic_year"
						v-model="form.academic_year"
						label="Ano acadêmico"
						type="text"
						maxlength="1"
						inputmode="numeric"
						pattern="[0-9]*"
						placeholder="Ex: 4º ano"
						required
						:error="errors.academic_year"
					/>

					<BaseInput
						ref="semesterInput"
						id="semester"
						v-model="form.semester"
						label="Semestre"
						type="text"
						maxlength="1"
						inputmode="numeric"
						pattern="[0-9]*"
						placeholder="Ex: 1º semestre"
						required
						:error="errors.semester"
					/>

					<BaseInput
						ref="calendarYearInput"
						id="calendar_year"
						v-model="form.calendar_year"
						label="Ano calendário"
						type="text"
						maxlength="4"
						inputmode="numeric"
						pattern="[0-9]*"
						placeholder="Ex: 2024"
						required
						:error="errors.calendar_year"
					/>

					<AppMultiselect
						v-model="form.specialties"
						:options="specialtiesOptions"
						field-label="Especialidades"
						mode="tags"
						label="label"
						track-by="value"
						value-prop="value"
						:searchable="true"
						:close-on-select="false"
						:can-clear="true"
						:append-to-body="true"
						placeholder="Selecione as especialidades"
						required
						:error="errors.specialties"
					/>
				</div>
			</div>

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