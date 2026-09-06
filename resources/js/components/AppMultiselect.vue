<script setup lang="ts">
import Multiselect from '@vueform/multiselect';

defineOptions({
	inheritAttrs: false,
});

withDefaults(
	defineProps<{
		fieldLabel?: string;
		showLabel?: boolean;
	}>(),
	{
		fieldLabel: undefined,
		showLabel: true,
	},
);
</script>

<template>
	<div>
		<label
			v-if="showLabel && fieldLabel"
			class="mb-1 block text-sm font-medium text-gray-700"
		>
			{{ fieldLabel }}
		</label>

		<Multiselect
			v-bind="$attrs"
			:no-options-text="'Nenhuma opção disponível'"
			:no-results-text="'Nenhum resultado encontrado'"
		/>
	</div>
</template>