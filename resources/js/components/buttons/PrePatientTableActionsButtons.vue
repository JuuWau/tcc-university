<script setup lang="ts">

import { Pencil, Trash2, UserPlus } from 'lucide-vue-next';

const props = defineProps<{
	params: {
		canDelete: boolean;
		data?: {
			status?: string;
		} & Record<string, any>;
		onEdit?: (row: any) => void;
		onDelete?: (row: any) => void;
		onConvert?: (row: any) => void;
	};
}>();

function edit() {
	if (!props.params.data) return;

	props.params.onEdit?.(props.params.data);
}

function remove() {
	if (!props.params.data) return;

	props.params.onDelete?.(props.params.data);
}

function convert() {
	if (!props.params.data) return;

	props.params.onConvert?.(props.params.data);
}

</script>

<template>

	<div class="flex h-full items-center justify-center gap-2">
		<Pencil
			v-if="props.params.data?.status !== 'converted'"
			class="cursor-pointer text-blue-500 hover:text-blue-700"
			:size="18"
			title="Editar pré-paciente"
			@click="edit"
		/>

		<UserPlus
			v-if="props.params.data?.status === 'waiting'"
			class="cursor-pointer text-green-600 hover:text-green-800"
			:size="18"
			title="Converter em paciente"
			@click="convert"
		/>

		<Trash2
			v-if="
				props.params.canDelete &&
				props.params.data?.status !== 'converted'
			"
			class="cursor-pointer text-red-500 hover:text-red-700"
			:size="18"
			title="Excluir pré-paciente"
			@click="remove"
		/>
	</div>
</template>

