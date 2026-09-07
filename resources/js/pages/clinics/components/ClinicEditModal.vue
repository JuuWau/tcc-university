<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { ClinicEditKey, RefreshTableKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { clinicSchema } from '@/schemas/clinic.schema';
import type { Clinic } from '@/types/clinic/clinic';
import axios from 'axios';
import { inject, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const editModal = inject<any>(ClinicEditKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!editModal) {
    throw new Error('ClinicEditModal precisa estar dentro do provider');
}

const form = reactive({
    id: null as number | null,
    name: '',
    specialty_ids: [] as number[],
});

const specialtyOptions = ref<
    {
        label: string;
        value: number;
    }[]
>([]);

watch(
    () => editModal.isOpen.value,
    (open) => {
        if (!open) return;

        const clinic = editModal.clinic.value as Clinic | null;

        if (!clinic) return;

        form.id = clinic.id;
        form.name = clinic.name;

        form.specialty_ids = clinic.specialties?.map(
            (specialty) => specialty.id,
        ) ?? [];
    },
);

function close() {
    editModal.isOpen.value = false;

    form.id = null;
    form.name = '';
    form.specialty_ids = [];
}

async function submit() {
    if (!form.id || loading.value) return;

    const result = clinicSchema.safeParse({
        name: form.name,
        specialty_ids: form.specialty_ids,
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;

        await axios.put(
            `/clinics/${form.id}`,
            result.data,
        );
        refreshTableRef?.value?.();

        toast.success('Clínica atualizada com sucesso');

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao atualizar clínica',
        );
    } finally {
        loading.value = false;
    }
}

async function loadSpecialties() {
    try {
        const { data } = await axios.get('/specialties/options');

        specialtyOptions.value = data.specialties.map(
            (specialty: any) => ({
                label: specialty.name,
                value: specialty.id,
            }),
        );
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao carregar especialidades',
        );
    }
}

onMounted(() => {
    loadSpecialties();
});
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
				title="Editar clínica"
				subtitle="Atualize o nome e as especialidades da clínica."
			/>

			<div class="min-h-0 flex-1 px-6">
				<div class="space-y-4 py-5">
					<BaseInput
						v-model="form.name"
						label="Nome da clínica (*)"
						type="text"
						maxlength="120"
						placeholder="Nome da clínica"
					/>

					<AppMultiselect
						v-model="form.specialty_ids"
						:options="specialtyOptions"
						field-label="Especialidades (*)"
						label="label"
						value-prop="value"
						mode="tags"
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
