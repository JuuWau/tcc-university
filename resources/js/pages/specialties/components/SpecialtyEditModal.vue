<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import {
    RefreshTableKey,
    SpecialtyEditKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { specialtySchema } from '@/schemas/specialty.schema';
import axios from 'axios';
import { inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

const editModal = inject<any>(SpecialtyEditKey);

if (!editModal) {
    throw new Error('SpecialtyEditModal precisa estar dentro do provider');
}

const form = reactive({
    id: null as number | null,
    name: '',
});

const loading = inject(LoadingKey)!;
const refreshTableRef = inject(RefreshTableKey);

if (!loading) {
    throw new Error('SpecialtyEditModal precisa estar dentro do provider de loading');
}

watch(
    () => editModal.isOpen.value,
    (open) => {
        if (!open) return;

        const specialty = editModal.specialty.value;

        if (!specialty) return;

        form.id = specialty.id;
        form.name = specialty.name;
    },
);

function close() {
    editModal.isOpen.value = false;
}

async function submit() {
    if (!form.id || loading.value) return;

    const result = specialtySchema.safeParse({
        name: form.name,
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;

        await axios.put(`/specialties/${form.id}`, {
            name: form.name,
        });

        refreshTableRef?.value?.();
        toast.success('Especialidade atualizada com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao atualizar especialidade',
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
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar especialidade"
				subtitle="Atualize o nome da especialidade."
			/>

			<div class="px-6">
				<div class="py-5">
					<BaseInput
						v-model="form.name"
						label="Nome da especialidade (*)"
						type="text"
						placeholder="Nome da especialidade"
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