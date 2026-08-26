<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ClinicCreateKey, ClinicsGroupKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { clinicSchema } from '@/schemas/clinic.schema';
import axios from 'axios';
import { inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject<any>(ClinicCreateKey);
const clinics = inject<any>(ClinicsGroupKey);
const loading = inject(LoadingKey);
const specialtyOptions = ref<
    {
        label: string;
        value: number;
    }[]
>([]);

if (!createModal) {
    throw new Error('ClinicCreateModal precisa estar dentro do provider');
}

const form = reactive({
    name: '',
    specialty_ids: [] as number[],
});

function close() {
    createModal.isOpen.value = false;
    form.name = '';
    form.specialty_ids = [];
}

watch(
    () => createModal.isOpen.value,
    async (open) => {
        if (!open) return;

        if (!specialtyOptions.value.length) {
            await loadSpecialties();
        }
    },
);

async function submit() {
    if (loading.value) return;

    const result = clinicSchema.safeParse(form);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const res = await axios.post('/clinics', result.data);
        clinics.value.unshift(res.data.clinic);
        toast.success('Clínica criada com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao criar clínica');
    } finally {
        loading.value = false;
    }
}

async function loadSpecialties() {
    try {
        const { data } = await axios.get('/specialties/options');

        specialtyOptions.value = data.specialties.map((specialty: any) => ({
            label: specialty.name,
            value: specialty.id,
        }));
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao carregar especialidades',
        );
    }
}
</script>

<template>
    <div
        v-if="createModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Nova Clínica</h2>
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
                    placeholder="Ex: Clínica Escola A"
                />
            </div>
            <div class="pb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Especialidades (*)
                </label>
                <AppMultiselect
                    v-model="form.specialty_ids"
                    :options="specialtyOptions"
                    label="label"
                    mode="tags"
                    value-prop="value"
                    placeholder="Selecione as especialidades"
                    :searchable="true"
                    :can-clear="true"
                    :multiple="true"
                    :append-to-body="true"
                />
            </div>
            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
