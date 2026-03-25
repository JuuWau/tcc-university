<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import type { ProcedureSpecialtyOption } from '@/keys/procedures/procedureKeys';
import {
    ProcedureEditKey,
    ProceduresGroupKey,
    ProceduresSpecialtiesKey,
} from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { procedureSchema } from '@/schemas/procedure.schema';
import type { Procedure } from '@/types/procedure';
import Multiselect from '@vueform/multiselect';
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
const procedures = inject(ProceduresGroupKey);

if (!editModal) {
    throw new Error('ProcedureEditModal precisa estar dentro do provider');
}

const form = reactive({
    id: null as number | null,
    name: '' as string,
    specialty_id: null as number | null,
});

watch(
    () => editModal.procedure.value,
    (procedure: Procedure | null) => {
        if (!procedure) return;
        form.id = procedure.id;
        form.name = procedure.name;
        form.specialty_id =
            procedure.specialty_id ?? procedure.specialty?.id ?? null;
    },
    { immediate: true },
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
        const index = procedures?.value?.findIndex(
            (p: Procedure) => p.id === form.id,
        );
        if (index !== undefined && index !== -1 && procedures?.value) {
            procedures.value[index] = {
                ...procedures.value[index],
                name: form.name,
                specialty_id: form.specialty_id ?? 0,
                specialty: specialtiesOptions.value.find(
                    (s) => s.value === form.specialty_id,
                )
                    ? {
                          id: form.specialty_id!,
                          name: specialtiesOptions.value.find(
                              (s) => s.value === form.specialty_id,
                          )!.label,
                      }
                    : procedures.value[index].specialty,
            };
        }
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Editar Procedimento</h2>
            <hr />
            <div class="space-y-4 py-4">
                <div>
                    <label
                        for="edit_procedure_name"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Nome (*)
                    </label>
                    <input
                        id="edit_procedure_name"
                        v-model="form.name"
                        type="text"
                        maxlength="255"
                        class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                        placeholder="Ex: Anamnese"
                    />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Especialidade (*)
                    </label>
                    <Multiselect
                        v-model="form.specialty_id"
                        :options="specialtiesOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        placeholder="Selecione a especialidade"
                    />
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click="submit" />
            </div>
        </div>
    </div>
</template>
