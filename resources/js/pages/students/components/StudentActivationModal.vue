<script setup lang="ts">
import ActivationButton from '@/components/buttons/ActivationButton.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import { STUDENT_ACTIVATION_REASONS } from '@/constants/studentActivationReason';
import {
    RefreshTableKey,
    StudentActivateKey,
} from '@/keys/students/studentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { studentActivationSchema } from '@/schemas/studentActivation.schema';
import axios from 'axios';
import { computed, inject, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const activationModal = inject<any>(StudentActivateKey);
const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const loading = inject(LoadingKey);
const selectedReason = ref<string | null>(null);
const otherReasonText = ref('');

const reasonOptions = computed(() =>
    Object.entries(STUDENT_ACTIVATION_REASONS).map(([value, data]) => ({
        value,
        label: data.label,
    })),
);

const selectedReasonData = computed(() => {
    if (!selectedReason.value) return null;
    return STUDENT_ACTIVATION_REASONS[
        selectedReason.value as keyof typeof STUDENT_ACTIVATION_REASONS
    ];
});

if (!activationModal || !loading) {
    throw new Error('StudentActivationModal precisa estar dentro do provider');
}

function close() {
    activationModal.isOpen.value = false;
}

watch(selectedReason, () => {
    otherReasonText.value = '';
});

async function confirmActivation() {
    if (
        !activationModal.student.value ||
        loading?.value ||
        !selectedReason.value
    ) {
        return;
    }

    const payload = {
        reason: selectedReason.value,
        note: otherReasonText.value?.trim() || null,
    };

    const parsed = studentActivationSchema.safeParse(payload);

    if (!parsed.success) {
        toast.error(parsed.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.delete(
            `/students/activate/${activationModal.student.value.id}`,
            { data: parsed.data },
        );

        toast.success('Aluno ativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="activationModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold text-gray-800">Ativar Aluno</h2>
            <hr />

            <div class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja ativar?
                <strong>{{ activationModal.student?.person?.name }}</strong>
            </div>

            <div>
                <label class="mb-6 pt-3 text-sm text-gray-600">
                    Motivo da ativação
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
                <ActivationButton
                    :loading="loading"
                    class="bg-green-600 hover:bg-green-700"
                    @click="confirmActivation()"
                >
                    Ativar
                </ActivationButton>
            </div>
        </div>
    </div>
</template>
