<script setup lang="ts">
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import type { Specialty } from '@/types/specialty';

defineProps<{
	specialty: Specialty;
}>();

const emit = defineEmits<{
	edit: [specialty: Specialty];
	delete: [specialty: Specialty];
}>();
</script>

<template>
	<div
		class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
	>
		<div
			class="flex items-center justify-between gap-3"
		>
			<div class="min-w-0">
				<p class="text-xs text-gray-500">
					Especialidade
				</p>

				<p
					class="mt-1 truncate text-sm font-medium text-gray-900"
				>
					{{ specialty.name }}
				</p>
			</div>

			<ActionsButtons
				:params="{
					data: specialty,
					onEdit: (specialty) => emit('edit', specialty),
					onDelete: (specialty) => emit('delete', specialty),
				}"
			/>
		</div>
	</div>
</template>
```
