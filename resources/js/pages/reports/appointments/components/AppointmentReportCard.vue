<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
	appointment: any;
}>();

const patient = computed(() => props.appointment.patient);
const student = computed(() => props.appointment.student);
const period = computed(() => props.appointment.enrollment?.slot?.period);

const responsible = computed(() => {
	const responsibles = props.appointment.slot?.responsibles;

	if (!responsibles?.length) {
		return '—';
	}

	return (
		responsibles
			.map((user: any) => user.person?.name)
			.filter(Boolean)
			.join(', ') || '—'
	);
});

const periodLabel = computed(() => {
	if (!period.value) {
		return '—';
	}

	return `${period.value.academic_year}º Ano - ${period.value.semester}º Semestre de ${period.value.calendar_year}`;
});

const clinicLabel = computed(() => {
	return props.appointment.slot?.clinic?.name ?? '—';
});

const dateLabel = computed(() => {
	if (!props.appointment.scheduled_start_at) {
		return '—';
	}

	return new Date(
		props.appointment.scheduled_start_at,
	).toLocaleDateString('pt-BR');
});

const timeLabel = computed(() => {
	if (
		!props.appointment.scheduled_start_at ||
		!props.appointment.scheduled_end_at
	) {
		return '—';
	}

	const start = new Date(
		props.appointment.scheduled_start_at,
	).toLocaleTimeString('pt-BR', {
		hour: '2-digit',
		minute: '2-digit',
	});

	const end = new Date(
		props.appointment.scheduled_end_at,
	).toLocaleTimeString('pt-BR', {
		hour: '2-digit',
		minute: '2-digit',
	});

	return `${start} - ${end}`;
});

const statusMap: Record<
	string,
	{
		label: string;
		classes: string;
	}
> = {
	scheduled: {
		label: 'Agendado',
		classes: 'bg-sky-200 text-sky-900',
	},

	confirmed: {
		label: 'Confirmado',
		classes: 'bg-green-200 text-green-900',
	},

	completed: {
		label: 'Concluído',
		classes: 'bg-teal-300 text-teal-900',
	},

	canceled: {
		label: 'Cancelado',
		classes: 'bg-red-200 text-red-900',
	},

	no_show: {
		label: 'Não compareceu',
		classes: 'bg-slate-300 text-slate-900',
	},

	rescheduled: {
		label: 'Remarcado',
		classes: 'bg-amber-300 text-amber-900',
	},
};

const status = computed(() =>
	statusMap[props.appointment.status] ?? {
		label: props.appointment.status ?? '-',
		classes: 'bg-gray-100 text-gray-800',
	},
);
</script>

<template>
	<div
		class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
	>
		<div class="flex items-start justify-between gap-3">
			<div class="min-w-0 flex-1">
				<p class="truncate text-sm font-semibold text-gray-900">
					{{ patient?.name ?? '—' }}
				</p>

				<p class="mt-1 text-xs text-gray-500">
					Código: {{ patient?.code ?? '—' }}
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

		<div
			class="mt-4 grid grid-cols-1 gap-3 border-t border-gray-100 pt-3 sm:grid-cols-2"
		>
			<div>
				<p class="text-xs text-gray-500">
					Aluno
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ student?.registration ?? '—' }} -
					{{ student?.user?.person?.name ?? '—' }}
				</p>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Responsável
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ responsible }}
				</p>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Período
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ periodLabel }}
				</p>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Clínica
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ clinicLabel }}
				</p>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Data
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ dateLabel }}
				</p>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Horário
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ timeLabel }}
				</p>
			</div>
		</div>
	</div>
</template>
