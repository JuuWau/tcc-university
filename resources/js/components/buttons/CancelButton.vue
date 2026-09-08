<script setup lang="ts">
import { X } from 'lucide-vue-next';

defineProps<{
	label?: string;
}>();

const emit = defineEmits<{
	click: [];
}>();
</script>

<template>
	<button
		type="button"
		class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-1 active:scale-[0.98]"
		@click="emit('click')"
	>
		<X class="h-4 w-4" />

		<span>
			{{ label ?? 'Cancelar' }}
		</span>
	</button>
</template>