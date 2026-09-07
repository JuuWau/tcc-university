<script setup lang="ts">
import type { Student } from '@/types/student/student';
import { computed } from 'vue';

const props = defineProps<{
	student: Student;
}>();

const status = computed(() =>
	props.student.deleted_at
		? {
				label: 'Inativo',
				classes: 'bg-slate-300 text-slate-900',
			}
		: {
				label: 'Ativo',
				classes: 'bg-green-200 text-green-900',
			},
);

const invite = computed(() => {
	const studentInvite = props.student.user?.invite;

	if (!studentInvite) {
		return {
			label: '—',
			classes: 'bg-gray-100 text-gray-800',
		};
	}

	return studentInvite.used_at
		? {
				label: 'Aceito',
				classes: 'bg-green-100 text-green-800',
			}
		: {
				label: 'Pendente',
				classes: 'bg-amber-100 text-amber-800',
			};
});

const periodLabel = computed(() => {
	const period = props.student.current_period?.period;

	if (!period) {
		return '—';
	}

	return `${period.academic_year}º ano / ${period.semester}º semestre de ${period.calendar_year}`;
});

const createdAtLabel = computed(() => {
	if (!props.student.created_at) {
		return '—';
	}

	return new Date(props.student.created_at).toLocaleDateString('pt-BR');
});
</script>

<template>
	<div
		class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
	>
		<div class="flex items-start justify-between gap-3">
			<div class="min-w-0 flex-1">
				<p class="break-words text-sm font-semibold text-gray-900">
					{{ student.person?.name ?? '—' }}
				</p>

				<p class="mt-1 text-xs text-gray-500">
					RA: {{ student.registration ?? '—' }}
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
					Período
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ periodLabel }}
				</p>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Convite
				</p>

				<span
					:class="[
						invite.classes,
						'mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
					]"
				>
					{{ invite.label }}
				</span>
			</div>

			<div>
				<p class="text-xs text-gray-500">
					Cadastro
				</p>

				<p class="mt-1 text-sm font-medium text-gray-700">
					{{ createdAtLabel }}
				</p>
			</div>
		</div>
	</div>
</template>
