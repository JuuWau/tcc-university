<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import {
    SpecialtiesGroupKey,
    SpecialtyCreateKey,
} from '@/keys/specialties/specialtyKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { specialtySchema } from '@/schemas/specialty.schema';
import axios from 'axios';
import { inject, reactive } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject<any>(SpecialtyCreateKey);
const specialties = inject<any>(SpecialtiesGroupKey);
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

        const res = await axios.post('/specialties', {
            name: form.name,
        });

        specialties.value.unshift(res.data.specialty);

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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Nova Especialidade</h2>
            <hr />

            <div class="py-4">
                <label
                    for="specialty-name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Nome da especialidade (*)
                </label>

                <input
                    id="specialty-name"
                    v-model="form.name"
                    type="text"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Ex: Endodontia"
                />
            </div>
            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
