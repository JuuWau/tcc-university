<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';

withDefaults(
	defineProps<{
		loading?: boolean;
		action?: 'save' | 'delete';
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

		<SaveButton
			v-else
			:loading="loading"
			@click.stop="emit('save')"
		>
			{{ actionLabel || 'Salvar' }}
		</SaveButton>
	</div>
</template>