<script setup lang="ts">
import { computed } from 'vue';

interface Props {
	modelValue?: string | null;
	label?: string;
	placeholder?: string;
	rows?: number;
	disabled?: boolean;
	required?: boolean;
	error?: string;
	name?: string;
	id?: string;
	autocomplete?: string;
}

const props = withDefaults(defineProps<Props>(), {
	modelValue: '',
	label: '',
	placeholder: '',
	rows: 3,
	disabled: false,
	required: false,
	error: '',
	name: undefined,
	id: undefined,
	autocomplete: undefined,
});

const emit = defineEmits<{
	'update:modelValue': [value: string];
	input: [event: Event];
}>();

const textareaValue = computed({
	get: () => props.modelValue ?? '',
	set: (value) => emit('update:modelValue', value),
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

		<textarea
			:id="id"
			v-model="textareaValue"
			:name="name"
			:placeholder="placeholder"
			:rows="rows"
			:disabled="disabled"
			:required="required"
			:autocomplete="autocomplete"
			:class="[
				'w-full rounded-lg border bg-white px-3 py-2 text-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-gray-100',
				error
					? 'border-red-300 focus:border-red-500 focus:ring-red-100'
					: 'border-gray-300 focus:border-sky-500 focus:ring-sky-100',
			]"
			@input="emit('input', $event)"
		/>

		<p
			v-if="error"
			class="mt-1 text-sm text-red-600"
		>
			{{ error }}
		</p>
	</div>
</template>
