<script setup lang="ts">
import * as Icons from 'lucide-vue-next';
import { computed } from 'vue';

const emit = defineEmits<{
	click: [];
}>();

const props = defineProps<{
	id?: string;
	label?: string;
	icon?: keyof typeof Icons;
}>();

const IconComponent = computed(() => {
	return props.icon ? Icons[props.icon] : null;
});
</script>

<template>
	<div class="flex justify-start">
		<button
			:id="id"
			type="button"
			class="inline-flex h-9 w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 active:scale-[0.98]"
			@click="emit('click')"
		>
			<component
				v-if="IconComponent"
				:is="IconComponent"
				class="h-4 w-4"
			/>

			<span>
				{{ label ?? 'Criar' }}
			</span>
		</button>
	</div>
</template>