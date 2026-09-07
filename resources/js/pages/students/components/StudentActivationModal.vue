<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import ActivationButton from '@/components/buttons/ActivationButton.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
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
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Ativar aluno"
				subtitle="Informe o motivo da ativação do aluno."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-4 py-5">
					<div class="text-sm text-gray-600">
						Tem certeza que deseja ativar o aluno
						<strong class="font-semibold text-gray-900">
							{{ activationModal.student?.person?.name }}
						</strong>
						?
					</div>

					<AppMultiselect
						v-model="selectedReason"
						:options="reasonOptions"
						field-label="Motivo da ativação (*)"
						label="label"
						value-prop="value"
						placeholder="Selecione um motivo"
						:searchable="false"
						:close-on-select="true"
						:can-clear="false"
						:append-to-body="true"
					/>

					<div
						v-if="selectedReasonData"
						class="rounded-lg border border-gray-200 bg-gray-50 p-3"
					>
						<p class="text-xs leading-relaxed text-gray-600">
							{{ selectedReasonData.description }}
						</p>
					</div>

					<div v-if="selectedReasonData?.requiresNote">
						<label
							class="mb-1 block text-sm font-medium text-gray-700"
						>
							Descrição do motivo (*)
						</label>

						<textarea
							v-model="otherReasonText"
							rows="3"
							class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition placeholder:text-gray-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
							placeholder="Descreva o motivo da ativação"
						/>
					</div>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="activate"
				action-label="Ativar"
				@cancel="close"
				@activate="confirmActivation"
			/>
		</div>
	</div>
</template>
