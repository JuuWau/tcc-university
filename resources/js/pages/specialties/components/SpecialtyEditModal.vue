<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import {
    SpecialtiesGroupKey,
    SpecialtyEditKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { specialtySchema } from '@/schemas/specialty.schema';
import type { Specialty } from '@/types/specialty';
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

const loading = inject(LoadingKey);

const specialties = inject<any>(SpecialtiesGroupKey);

watch(
    () => editModal.specialty.value,
    (specialty: Specialty | null) => {
        if (!specialty) return;

        form.id = specialty.id;
        form.name = specialty.name;
    },
    { immediate: true },
);

function close() {
    editModal.isOpen.value = false;
}

async function submit() {
    console.log('aqui2');
    if (!form.id || loading.value) return;

    const result = specialtySchema.safeParse({
        name: form.name,
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        console.log('aqui');
        return;
    }

    try {
        loading.value = true;

        await axios.put(`/specialties/${form.id}`, {
            name: form.name,
        });

        const index = specialties.value.findIndex(
            (s: Specialty) => s.id === form.id,
        );

        if (index !== -1) {
            specialties.value[index].name = form.name;
        }

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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Editar Especialidade</h2>
            <hr />

            <div class="py-4">
                <label
                    for="specialty-name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Nome da especialidade (*)
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    class="mb-4 w-full rounded border px-3 py-2"
                    placeholder="Nome da especialidade"
                />
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click="submit" />
            </div>
        </div>
    </div>
</template>
