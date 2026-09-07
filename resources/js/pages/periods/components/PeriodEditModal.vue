<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { PeriodEditKey, RefreshTableKey } from '@/keys/periods/periodKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { periodSchema } from '@/schemas/period.schema';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { inject, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

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
const editModal = inject<any>(PeriodEditKey);

if (!editModal) {
    throw new Error('PeriodEditModal precisa estar dentro do provider');
}

const form = reactive({
    id: null as number | null,
    academic_year: '' as string | null,
    semester: '' as string | null,
    calendar_year: '' as string | null,
    specialties: [] as { label: string; value: number }[],
});

const loading = inject(LoadingKey);

const refreshTableRef = inject(RefreshTableKey);

watch(
    () => editModal.isOpen.value,
    (open) => {
        if (!open) return;

        const period = editModal.period.value;

        if (!period) return;

        form.id = period.id;
        form.academic_year = String(period.academic_year);
        form.semester = String(period.semester);
        form.calendar_year = String(period.calendar_year);

        form.specialties =
            period.specialties?.map((s) => ({
                label: s.name,
                value: s.id,
            })) ?? [];
    },
);

function close() {
    editModal.isOpen.value = false;
}

async function submit() {
    if (!form.id || loading.value) return;

    const result = periodSchema.safeParse({
        academic_year: form.academic_year,
        semester: form.semester,
        calendar_year: form.calendar_year,
        specialties: form.specialties.map((s) => s.value),
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        console.log('aqui');
        return;
    }

    try {
        loading.value = true;

		await axios.put(`/periods/${form.id}`, {
            academic_year: form.academic_year,
            semester: form.semester,
            calendar_year: form.calendar_year,
            specialties: form.specialties.map((s) => s.value),
        });

		refreshTableRef?.value?.();

        toast.success('Período atualizado com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao atualizar período',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="editModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar período"
				subtitle="Atualize os dados acadêmicos e as especialidades do período."
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
						:object="true"
						label="label"
						track-by="value"
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