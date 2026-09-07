<script setup lang="ts">
import LoadingSpinner from '@/components/ui/spinner/Spinner.vue';
import { Save } from 'lucide-vue-next';

defineProps<{
	loading?: boolean;
}>();
</script>

<template>
	<button
		type="button"
		:disabled="loading"
		class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
	>
		<LoadingSpinner
			v-if="loading"
			class="h-4 w-4"
		/>

		<Save
			v-else
			class="h-4 w-4"
		/>

		<span>
			{{ loading ? 'Salvando...' : 'Salvar' }}
		</span>
	</button>
</template>