<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import {
	RefreshTableKey,
    SpecialtyCreateKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { specialtySchema } from '@/schemas/specialty.schema';
import axios from 'axios';
import { inject, reactive } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject<any>(SpecialtyCreateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!createModal) {
    throw new Error('SpecialtyCreateModal precisa estar dentro do provider');
}

const form = reactive({
    name: '',
});

function close() {
    createModal.isOpen.value = false;
    form.name = '';
}

async function submit() {
    if (loading.value) return;

    const result = specialtySchema.safeParse(form);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;

		await axios.post('/specialties', {
            name: form.name,
        });

		refreshTableRef?.value?.();

        toast.success('Especialidade criada com sucesso');
        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao criar especialidade',
        );
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
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Nova especialidade"
				subtitle="Cadastre uma nova especialidade para o sistema."
			/>

			<div class="px-6">
				<div class="py-5">
					<BaseInput
						v-model="form.name"
						label="Nome da especialidade (*)"
						type="text"
						placeholder="Ex: Endodontia"
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
