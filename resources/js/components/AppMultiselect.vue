<script setup lang="ts">
import Multiselect from '@vueform/multiselect';

defineOptions({
	inheritAttrs: false,
});

withDefaults(
	defineProps<{
		fieldLabel?: string;
		showLabel?: boolean;
		required?: boolean;
	}>(),
	{
		fieldLabel: undefined,
		showLabel: true,
		required: false,
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
			<span
				v-if="required"
				class="text-red-500"
			>
				*
			</span>
		</label>

		<Multiselect
			v-bind="$attrs"
			:required="required"
			:no-options-text="'Nenhuma opção disponível'"
			:no-results-text="'Nenhum resultado encontrado'"
		/>
	</div>
</template>