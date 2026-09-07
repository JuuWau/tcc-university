<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

interface Props {
	modelValue?: string | number | null;
	label?: string;
	placeholder?: string;
	type?: string;
	disabled?: boolean;
	required?: boolean;
	error?: string;
	icon?: Component;
	name?: string;
	id?: string;
	autocomplete?: string;
}

const props = withDefaults(defineProps<Props>(), {
	modelValue: '',
	label: '',
	placeholder: '',
	type: 'text',
	disabled: false,
	required: false,
	error: '',
	icon: undefined,
	name: undefined,
	id: undefined,
	autocomplete: undefined,
});

const emit = defineEmits<{
	'update:modelValue': [value: string];
	input: [event: Event];
}>();

const inputValue = computed({
	get: () => props.modelValue ?? '',
	set: (value) => emit('update:modelValue', String(value)),
});
</script>

<template>
	<div class="w-full">
		<label
			v-if="label"
			:for="id"
			class="mb-1 block text-sm font-medium text-gray-700"
		>
			{{ label }}

			<span
				v-if="required"
				class="text-red-500"
			>
				*
			</span>
		</label>

		<div class="relative">
			<component
				v-if="icon"
				:is="icon"
				class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
			/>

			<input
				:id="id"
				v-model="inputValue"
				:name="name"
				:type="type"
				:placeholder="placeholder"
				:disabled="disabled"
				:required="required"
				:autocomplete="autocomplete"
				:class="[
					'w-full rounded-lg border bg-white py-2.5 pr-3 text-sm transition focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-gray-100',
					icon ? 'pl-9' : 'pl-3',
					error
						? 'border-red-300 focus:border-red-500 focus:ring-red-100'
						: 'border-gray-300 focus:border-sky-500 focus:ring-sky-100',
				]"
				@input="emit('input', $event)"
			/>
		</div>

		<p
			v-if="error"
			class="mt-1 text-sm text-red-600"
		>
			{{ error }}
		</p>
	</div>
</template>
