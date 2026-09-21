<script setup lang="ts">
const props = defineProps<{
	params?: {
		value?: string | null;
	};
}>();

const rawValue = String(props.params?.value ?? '').trim();
const normalizedValue = rawValue.toLowerCase();

const statusLabels: Record<string, string> = {
	waiting: 'Aguardando',
	converted: 'Convertido',
	cancelled: 'Cancelado',
};

const classes: Record<string, string> = {
	waiting: 'bg-yellow-100 text-yellow-800',
	converted: 'bg-green-100 text-green-800',
	cancelled: 'bg-red-100 text-red-800',
};

const label = (statusLabels[normalizedValue] ?? rawValue) || 'Desconhecido';
const className = classes[normalizedValue] ?? 'bg-gray-100 text-gray-800';
</script>

<template>
	<span
		class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
		:class="className"
	>
		{{ label }}
	</span>
</template>