<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ProcedureCreateKey, ProceduresGroupKey, ProceduresSpecialtiesKey } from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { procedureSchema } from '@/schemas/procedure.schema';
import axios from 'axios';
import Multiselect from '@vueform/multiselect';
import type { ProcedureSpecialtyOption } from '@/keys/procedures/procedureKeys';
import { computed, inject, reactive } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(ProcedureCreateKey);
const procedures = inject(ProceduresGroupKey);
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
        const res = await axios.post('/procedures', {
            name: form.name,
            specialty_id: form.specialty_id,
        });
        if (procedures?.value) {
            procedures.value.unshift(res.data.procedure);
        }
        toast.success('Procedimento criado com sucesso');
        close();
    } catch (error: unknown) {
        const err = error as {
            response?: {
                data?: { message?: string; errors?: Record<string, string[]> };
            };
        };
        const data = err.response?.data;
        const firstError = data?.errors
            ? Object.values(data.errors).flat()[0]
            : null;
        toast.error(firstError ?? data?.message ?? 'Erro ao criar procedimento');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="createModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Novo Procedimento</h2>
            <hr />
            <div class="space-y-4 py-4">
                <div>
                    <label
                        for="procedure_name"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Nome (*)
                    </label>
                    <input
                        id="procedure_name"
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
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
