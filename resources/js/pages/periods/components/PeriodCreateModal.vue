<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
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

function close() {
    createModal.isOpen.value = false;
    form.academic_year = null;
    form.semester = null;
    form.calendar_year = null;
    form.specialties = [];
}

async function submit() {
    if (loading.value) return;

    const result = periodSchema.safeParse(form);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
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
						id="academic_year"
						v-model="form.academic_year"
						label="Ano acadêmico (*)"
						type="text"
						maxlength="1"
						inputmode="numeric"
						pattern="[0-9]*"
						placeholder="Ex: 4º ano"
					/>

					<BaseInput
						id="semester"
						v-model="form.semester"
						label="Semestre (*)"
						type="text"
						maxlength="1"
						inputmode="numeric"
						pattern="[0-9]*"
						placeholder="Ex: 1º semestre"
					/>

					<BaseInput
						id="calendar_year"
						v-model="form.calendar_year"
						label="Ano calendário (*)"
						type="text"
						maxlength="4"
						inputmode="numeric"
						pattern="[0-9]*"
						placeholder="Ex: 2024"
					/>

					<AppMultiselect
						v-model="form.specialties"
						:options="specialtiesOptions"
						field-label="Especialidades (*)"
						mode="tags"
						label="label"
						track-by="value"
						value-prop="value"
						:searchable="true"
						:close-on-select="false"
						:can-clear="true"
						:append-to-body="true"
						placeholder="Selecione as especialidades"
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