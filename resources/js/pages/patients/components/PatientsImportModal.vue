<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import {
    PatientsImportKey,
    RefreshTableKey,
} from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { Check, X } from 'lucide-vue-next';
import { inject, ref } from 'vue';
import { toast } from 'vue3-toastify';

const importModal = inject(PatientsImportKey);

const refreshTableRef = inject<{ value: (() => void) | null }>(
    RefreshTableKey,
);

const loading = inject(LoadingKey);

if (!importModal) {
    throw new Error(
        'PatientsImportModal precisa estar dentro do provider',
    );
}

const modal = importModal;

const file = ref<File | null>(null);

const result = ref<{
    imported: number;
    errors: {
        sheet: string;
        row: number;
        patient: string | null;
        message: string;
    }[];
} | null>(null);

function handleFile(event: Event) {
    const target = event.target as HTMLInputElement;

    file.value = target.files?.[0] ?? null;
}

function close() {
    modal.isOpen.value = false;

    file.value = null;
    result.value = null;
}

function formatError(error: {
    sheet: string;
    row: number;
    patient: string | null;
    message: string;
}) {
    const parts = [
        `Aba: ${error.sheet}`,
        `Linha: ${error.row}`,
    ];

    if (error.patient) {
        parts.push(`Paciente: ${error.patient}`);
    }

    parts.push(error.message);

    return parts.join(' • ');
}

async function pollImport(importId: number) {
    const interval = setInterval(async () => {
        try {
            const { data } = await axios.get(
                `/patients/imports/${importId}`,
            );

            if (
                data.status === 'completed' ||
                data.status === 'failed'
            ) {
                clearInterval(interval);

                result.value = {
                    imported: data.imported,
                    errors: data.errors ?? [],
                };

                if (loading) {
                    loading.value = false;
                }

                toast.success('Importação finalizada');

                refreshTableRef?.value?.();
            }
        } catch {
            clearInterval(interval);

            if (loading) {
                loading.value = false;
            }

            toast.error(
                'Erro ao acompanhar importação',
            );
        }
    }, 2000);
}

async function submit() {
    if (!file.value || loading?.value) return;

    try {
        if (loading) loading.value = true;

        result.value = null;

        const formData = new FormData();

        formData.append('file', file.value);

        const { data } = await axios.post(
            '/patients/import',
            formData,
        );

        const importId = data.import_id;

        pollImport(importId);

        toast.success('Importação finalizada');

        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ??
                'Erro ao importar pacientes',
        );
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="w-full max-w-2xl rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">
                Importar pacientes
            </h2>

            <hr />

            <div class="space-y-4 py-4">
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Arquivo Excel
                    </label>

                    <input
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="w-full rounded border px-3 py-2 text-sm"
                        @change="handleFile"
                    />
                </div>

                <div
                    v-if="file"
                    class="rounded-lg border bg-gray-50 p-3"
                >
                    <p class="text-sm font-medium text-gray-700">
                        {{ file.name }}
                    </p>

                    <p
                        v-if="loading"
                        class="mt-1 text-sm text-sky-600"
                    >
                        Processando arquivo...
                    </p>
                </div>

                <div
                    v-if="result"
                    class="space-y-4 rounded-lg border p-4"
                >
                    <div class="flex gap-6">
                        <div class="flex items-center gap-1  text-sm text-green-600">
                            <Check class="h-4 w-4" />

                            <span>
                                {{ result.imported }} importados
                            </span>
                        </div>

                        <div class="flex items-center gap-1 text-sm text-red-600">
                            <X class="h-4 w-4" />

                            <span>
                                {{ result.errors.length }} erros
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="result.errors.length"
                        class="max-h-64 overflow-y-auto rounded border bg-red-50 p-3"
                    >
                        <div
                            v-for="error in result.errors"
                            :key="error"
                            class="mb-1 text-sm text-red-700"
                        >
                            {{ formatError(error) }}
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex justify-end gap-2 border-t border-gray-200 pt-4"
            >
                <CancelButton @click="close" />

                <SaveButton
                    :loading="loading"
                    @click.stop="submit"
                >
                    Importar
                </SaveButton>
            </div>
        </div>
    </div>
</template>