<script setup lang="ts">
import { computed } from 'vue';
import type { PatientForTab } from '@/types/patient/patient';

const props = defineProps<{
	patient: PatientForTab;
}>();

const statusMap: Record<
	string,
	{
		label: string;
		classes: string;
	}
> = {
	ativo: {
		label: 'Ativo',
		classes: 'bg-green-200 text-green-900',
	},

	inativo: {
		label: 'Inativo',
		classes: 'bg-slate-300 text-slate-900',
	},

	tratamento: {
		label: 'Tratamento',
		classes: 'bg-sky-200 text-sky-900',
	},

	pausa_tratamento: {
		label: 'Pausa no Tratamento',
		classes: 'bg-amber-200 text-amber-900',
	},

	abandono: {
		label: 'Abandono',
		classes: 'bg-red-200 text-red-900',
	},

	concluido: {
		label: 'Concluído',
		classes: 'bg-teal-300 text-teal-900',
	},

	transferencia: {
		label: 'Transferência',
		classes: 'bg-purple-200 text-purple-900',
	},
};

const status = computed(() =>
	statusMap[props.patient.status] ?? {
		label: props.patient.status ?? '-',
		classes: 'bg-gray-100 text-gray-800',
	},
);
</script>

<template>
	<div
		class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
	>
		<div class="flex items-start justify-between gap-3">
			<div class="min-w-0">
				<p class="break-words text-sm font-semibold text-gray-900">
					{{ patient.name }}
				</p>

				<p class="mt-1 text-xs text-gray-500">
					Código: {{ patient.code }}
				</p>
			</div>

			<span
				:class="[
					status.classes,
					'shrink-0 rounded-full px-2.5 py-1 text-xs font-medium',
				]"
			>
				{{ status.label }}
			</span>
		</div>
	</div>
</template>
