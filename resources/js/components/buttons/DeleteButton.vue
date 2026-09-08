```vue
<script setup lang="ts">
import LoadingSpinner from '@/components/ui/spinner/Spinner.vue';
import { Trash2 } from 'lucide-vue-next';

withDefaults(
	defineProps<{
		loading?: boolean;
		label?: string;
	}>(),
	{
		loading: false,
		label: 'Excluir',
	},
);

const emit = defineEmits<{
	click: [];
}>();
</script>

<template>
	<button
		type="button"
		:disabled="loading"
		class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
		@click="emit('click')"
	>
		<LoadingSpinner
			v-if="loading"
			class="h-4 w-4"
		/>

		<Trash2
			v-else
			class="h-4 w-4"
		/>

		<span>
			{{ loading ? 'Excluindo...' : label }}
		</span>
	</button>
</template>
```
