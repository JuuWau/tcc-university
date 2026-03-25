<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ClinicEditKey, ClinicsGroupKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { clinicSchema } from '@/schemas/clinic.schema';
import type { Clinic } from '@/types/clinic/clinic';
import axios from 'axios';
import { inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

const editModal = inject<any>(ClinicEditKey);
const clinics = inject<any>(ClinicsGroupKey);
const loading = inject(LoadingKey);

if (!editModal) {
    throw new Error('ClinicEditModal precisa estar dentro do provider');
}

const form = reactive({
    id: null as number | null,
    name: '',
});

watch(
    () => editModal.clinic.value,
    (clinic: Clinic | null) => {
        if (!clinic) return;
        form.id = clinic.id;
        form.name = clinic.name;
    },
    { immediate: true },
);

function close() {
    editModal.isOpen.value = false;
}

async function submit() {
    if (!form.id || loading.value) return;

    const result = clinicSchema.safeParse({ name: form.name });
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const res = await axios.put(`/clinics/${form.id}`, result.data);
        const index = clinics.value.findIndex((clinic: Clinic) => clinic.id === form.id);
        if (index !== -1) clinics.value[index] = res.data.clinic;
        toast.success('Clínica atualizada com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao atualizar clínica');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="editModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Editar Clínica</h2>
            <hr />
            <div class="py-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Nome da clínica (*)
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    maxlength="120"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Nome da clínica"
                />
            </div>
            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
