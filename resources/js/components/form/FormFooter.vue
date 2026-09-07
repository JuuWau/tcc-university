<script setup lang="ts">
import ActivationButton from '@/components/buttons/ActivationButton.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeactivateButton from '@/components/buttons/DeactivateButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';

withDefaults(
	defineProps<{
		loading?: boolean;
		action?: 'save' | 'delete' | 'deactivate' | 'activate';
		actionLabel?: string;
	}>(),
	{
		loading: false,
		action: 'save',
		actionLabel: '',
	},
);

const emit = defineEmits<{
	cancel: [];
	save: [];
	delete: [];
	deactivate: [];
	activate: [];
}>();
</script>

<template>
	<div
		class="flex shrink-0 justify-end gap-2 border-t border-gray-200 bg-white p-4"
	>
		<CancelButton @click="emit('cancel')" />

		<DeleteButton
			v-if="action === 'delete'"
			:loading="loading"
			@click="emit('delete')"
		>
			{{ actionLabel || 'Excluir' }}
		</DeleteButton>

		<DeactivateButton
			v-else-if="action === 'deactivate'"
			type="button"
			:disabled="loading"
			@click="emit('deactivate')"
		>
			{{ loading ? 'Inativando...' : actionLabel || 'Inativar' }}
		</DeactivateButton>

		<ActivationButton
			v-else-if="action === 'activate'"
			type="button"
			:disabled="loading"
			@click="emit('activate')"
		>
			{{ loading ? 'Ativando...' : actionLabel || 'Ativar' }}
		</ActivationButton>

		<SaveButton
			v-else
			:loading="loading"
			@click.stop="emit('save')"
		>
			{{ actionLabel || 'Salvar' }}
		</SaveButton>
	</div>
</template>
