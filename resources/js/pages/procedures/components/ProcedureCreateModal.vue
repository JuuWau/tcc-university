<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ProcedureCreateKey, ProceduresSpecialtiesKey, RefreshTableKey } from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { procedureSchema } from '@/schemas/procedure.schema';
import axios from 'axios';
import type { ProcedureSpecialtyOption } from '@/keys/procedures/procedureKeys';
import { computed, inject, reactive } from 'vue';
import { toast } from 'vue3-toastify';
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormFooter from '@/components/form/FormFooter.vue';

const createModal = inject(ProcedureCreateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);
const specialtiesFromProvider = inject(ProceduresSpecialtiesKey, [] as ProcedureSpecialtyOption[]);
const specialtiesOptions = computed(() =>
    specialtiesFromProvider.map((s) => ({ label: s.name, value: s.id })),
);

if (!createModal) {
    throw new Error('ProcedureCreateModal precisa estar dentro do provider');
}

const form = reactive({
    name: '' as string,
    specialty_id: null as number | null,
});

function close() {
    createModal!.isOpen.value = false;
    form.name = '';
    form.specialty_id = null;
}

async function submit() {
    if (loading?.value) return;

    const result = procedureSchema.safeParse({
        name: form.name,
        specialty_id: form.specialty_id,
    });
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;
		await axios.post('/procedures', {
            name: form.name,
            specialty_id: form.specialty_id,
        });
		refreshTableRef?.value?.();
        toast.success('Procedimento criado com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao criar procedimento');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="createModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Novo procedimento"
				subtitle="Cadastre o procedimento e associe uma especialidade."
			/>

			<div class="min-h-0 flex-1 px-6">
				<div class="space-y-4 py-5">
					<BaseInput
						id="procedure_name"
						v-model="form.name"
						label="Nome (*)"
						type="text"
						maxlength="255"
						placeholder="Ex: Anamnese"
					/>

					<AppMultiselect
						v-model="form.specialty_id"
						:options="specialtiesOptions"
						field-label="Especialidade (*)"
						label="label"
						value-prop="value"
						:searchable="true"
						:close-on-select="true"
						:can-clear="true"
						:append-to-body="true"
						placeholder="Selecione a especialidade"
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
