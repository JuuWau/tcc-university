<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { ClinicCreateKey, RefreshTableKey } from '@/keys/clinics/clinicKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { clinicSchema } from '@/schemas/clinic.schema';
import axios from 'axios';
import { inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject<any>(ClinicCreateKey);
const refreshTableRef = inject(RefreshTableKey);
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

const errors = reactive({
    name: '',
    specialty_ids: '',
});

const nameInput = ref<{ focus: () => void } | null>(null);
const specialtyInput = ref<{ focus: () => void } | null>(null);

function clearErrors() {
    errors.name = '';
    errors.specialty_ids = '';
}

function close() {
    createModal.isOpen.value = false;

    form.name = '';
    form.specialty_ids = [];

    clearErrors();
}

watch(
    () => createModal.isOpen.value,
    async (open) => {
        if (!open) return;

        clearErrors();

        if (!specialtyOptions.value.length) {
            await loadSpecialties();
        }
    },
);

async function submit() {
    if (loading.value) return;

    clearErrors();

    const result = clinicSchema.safeParse(form);

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0];

            if (field === 'name' || field === 'specialty_ids') {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0];

        if (firstError.path[0] === 'name') {
            nameInput.value?.focus();
        } else if (firstError.path[0] === 'specialty_ids') {
            specialtyInput.value?.focus();
        }

        return;
    }

    try {
        loading.value = true;

        await axios.post('/clinics', result.data);

        refreshTableRef?.value?.();

        toast.success('Clínica criada com sucesso');

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao criar clínica',
        );
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Nova clínica"
                subtitle="Cadastre a clínica e suas especialidades."
            />

            <div class="min-h-0 flex-1 px-6">
                <div class="space-y-4 py-5">
                    <BaseInput
                        ref="nameInput"
                        v-model="form.name"
                        label="Nome da clínica"
                        type="text"
                        maxlength="120"
                        placeholder="Ex: Clínica Escola A"
                        required
                        :error="errors.name"
                    />

                    <AppMultiselect
                        ref="specialtyInput"
                        v-model="form.specialty_ids"
                        :options="specialtyOptions"
                        field-label="Especialidades"
                        label="label"
                        value-prop="value"
                        mode="tags"
                        :searchable="true"
                        :close-on-select="false"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Selecione as especialidades"
                        required
                        :error="errors.specialty_ids"
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