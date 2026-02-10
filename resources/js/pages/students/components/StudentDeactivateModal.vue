<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeactivateButton from '@/components/buttons/DeactivateButton.vue';
import { STUDENT_REASONS } from '@/constants/studentReason';
import {
    StudentDeactivateKey,
    RefreshTableKey,
} from '@/keys/students/studentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { studentDeactivateSchema } from '@/schemas/studentDeactivate.schema';
import axios from 'axios';
import { computed, inject, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const deactivateModal = inject<any>(StudentDeactivateKey);
const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const loading = inject(LoadingKey);
const selectedReason = ref<string | null>(null);
const otherReasonText = ref('');

const reasonOptions = computed(() =>
    Object.entries(STUDENT_REASONS).map(([value, data]) => ({
        value,
        label: data.label,
    })),
);

const selectedReasonData = computed(() => {
    if (!selectedReason.value) return null;
    return STUDENT_REASONS[selectedReason.value as keyof typeof STUDENT_REASONS];
});

if (!deactivateModal || !loading) {
    throw new Error('StudentDeactivateModal precisa estar dentro do provider');
}

function close() {
    deactivateModal.isOpen.value = false;
}

watch(selectedReason, () => {
    otherReasonText.value = '';
});

async function confirmDelete() {
    if (
        !deactivateModal.student.value ||
        loading?.value ||
        !selectedReason.value
    ) {
        return;
    }

    const payload = {
        reason: selectedReason.value,
        note: otherReasonText.value?.trim() || null,
    };

    const parsed = studentDeactivateSchema.safeParse(payload);

    if (!parsed.success) {
        toast.error(parsed.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.delete(
            `/students/deactivate/${deactivateModal.student.value.id}`,
            { data: parsed.data },
        );

        toast.success('Aluno inativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="deactivateModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold text-gray-800">Inativar Aluno</h2>
            <hr />

            <div class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja inativar?
                <strong>{{ deactivateModal.student?.person?.name }}</strong>
            </div>

            <div>
                <label class="mb-6 pt-3 text-sm text-gray-600">
                    Motivo da inativação
                </label>

                <Multiselect
                    v-model="selectedReason"
                    :options="reasonOptions"
                    label="label"
                    value-prop="value"
                    placeholder="Selecione um motivo"
                    :searchable="false"
                    :close-on-select="true"
                />
            </div>

            <p v-if="selectedReasonData" class="mt-1 text-xs text-gray-500">
                {{ selectedReasonData.description }}
            </p>

            <div v-if="selectedReasonData?.requiresNote" class="mt-3">
                <label class="pt-3 text-sm text-gray-600">
                    Descrição do motivo
                </label>

                <textarea
                    v-model="otherReasonText"
                    rows="3"
                    class="w-full rounded-md border border-gray-300 p-2 text-sm"
                    placeholder="Descreva o motivo da inativação"
                />
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <CancelButton @click="close" />
                <DeactivateButton
                    :loading="loading"
                    class="bg-yellow-600 hover:bg-yellow-700"
                    @click="confirmDelete"
                >
                    Inativar
                </DeactivateButton>
            </div>
        </div>
    </div>
</template>
