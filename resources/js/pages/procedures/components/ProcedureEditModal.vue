<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import type { ProcedureSpecialtyOption } from '@/keys/procedures/procedureKeys';
import { ProcedureEditKey, ProceduresSpecialtiesKey, RefreshTableKey } from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { procedureSchema } from '@/schemas/procedure.schema';
import type { Procedure } from '@/types/procedure';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

const specialtiesFromProvider = inject(
    ProceduresSpecialtiesKey,
    [] as ProcedureSpecialtyOption[],
);
const specialtiesOptions = computed(() =>
    specialtiesFromProvider.map((s) => ({ label: s.name, value: s.id })),
);

const editModal = inject(ProcedureEditKey);
const loading = inject(LoadingKey);
const refreshTableRef = inject(RefreshTableKey);

if (!editModal) {
    throw new Error('ProcedureEditModal precisa estar dentro do provider');
}

const form = reactive({
    id: null as number | null,
    name: '' as string,
    specialty_id: null as number | null,
});

watch(
    () => editModal.isOpen.value,
    (open) => {
        if (!open) return;

        const procedure = editModal.procedure.value;

        if (!procedure) return;

        form.id = procedure.id;
        form.name = procedure.name;
        form.specialty_id =
            procedure.specialty_id ?? procedure.specialty?.id ?? null;
    },
);

function close() {
    editModal!.isOpen.value = false;
}

async function submit() {
    if (!form.id || loading?.value) return;

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
        await axios.put(`/procedures/${form.id}`, {
            name: form.name,
            specialty_id: form.specialty_id,
        });
        refreshTableRef?.value?.();
        toast.success('Procedimento atualizado com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao atualizar procedimento',
        );
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="editModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar procedimento"
				subtitle="Atualize o nome e a especialidade do procedimento."
			/>

			<div class="min-h-0 flex-1 px-6">
				<div class="space-y-4 py-5">
					<BaseInput
						id="edit_procedure_name"
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
