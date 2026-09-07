<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeactivateButton from '@/components/buttons/DeactivateButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
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
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Inativar aluno"
				subtitle="Informe o motivo da inativação do aluno."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-4 py-5">
					<div class="text-sm text-gray-600">
						Tem certeza que deseja inativar o aluno
						<strong class="font-semibold text-gray-900">
							{{ deactivateModal.student?.person?.name }}
						</strong>
						?
					</div>

					<AppMultiselect
						v-model="selectedReason"
						:options="reasonOptions"
						field-label="Motivo da inativação (*)"
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
							placeholder="Descreva o motivo da inativação"
						/>
					</div>
				</div>
			</div>

			<FormFooter
				:loading="loading"
				action="deactivate"
				action-label="Inativar"
				@cancel="close"
				@deactivate="confirmDelete"
			/>
		</div>
	</div>
</template>
