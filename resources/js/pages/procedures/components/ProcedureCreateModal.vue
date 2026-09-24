<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { ProcedureCreateKey, ProceduresSpecialtiesKey, RefreshTableKey } from '@/keys/procedures/procedureKeys';
import type { ProcedureSpecialtyOption } from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { procedureSchema } from '@/schemas/procedure.schema';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(ProcedureCreateKey);

const refreshTableRef = inject(RefreshTableKey);

const loading = inject(LoadingKey);

const specialtiesFromProvider = inject(
    ProceduresSpecialtiesKey,
    [] as ProcedureSpecialtyOption[],
);

const specialtiesOptions = computed(() =>
    specialtiesFromProvider.map((s) => ({
        label: s.name,
        value: s.id,
    })),
);

if (!createModal) {
    throw new Error('ProcedureCreateModal precisa estar dentro do provider');
}

const form = reactive({
    name: '' as string,
    specialty_id: null as number | null,
});

const errors = reactive({
    name: '',
    specialty_id: '',
});

const nameInput = ref<{ focus: () => void } | null>(null);

const specialtyInput = ref<{ focus: () => void } | null>(null);

function clearErrors() {
    errors.name = '';
    errors.specialty_id = '';
}

function close() {
    createModal.isOpen.value = false;

    form.name = '';
    form.specialty_id = null;

    clearErrors();
}

watch(
    () => createModal.isOpen.value,
    (open) => {
        if (!open) return;

        clearErrors();
    },
);

async function submit() {
    if (loading?.value) return;

    clearErrors();

    const result = procedureSchema.safeParse({
        name: form.name,
        specialty_id: form.specialty_id,
    });

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0];

            if (field === 'name' || field === 'specialty_id') {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0];

        if (firstError.path[0] === 'name') {
            nameInput.value?.focus();
        } else if (firstError.path[0] === 'specialty_id') {
            specialtyInput.value?.focus();
        }

        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.post('/procedures', {
            name: form.name,
            specialty_id: form.specialty_id,
        });

        refreshTableRef?.value?.();

        toast.success('Procedimento criado com sucesso');

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao criar procedimento',
        );
    } finally {
        if (loading) loading.value = false;
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
                title="Novo procedimento"
                subtitle="Cadastre o procedimento e associe uma especialidade."
            />

            <div class="min-h-0 flex-1 px-6">
                <div class="space-y-4 py-5">
                    <BaseInput
                        ref="nameInput"
                        id="procedure_name"
                        v-model="form.name"
                        label="Nome"
                        type="text"
                        maxlength="255"
                        placeholder="Ex: Anamnese"
                        required
                        :error="errors.name"
                    />

                    <AppMultiselect
                        ref="specialtyInput"
                        v-model="form.specialty_id"
                        :options="specialtiesOptions"
                        field-label="Especialidade"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Selecione a especialidade"
                        required
                        :error="errors.specialty_id"
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