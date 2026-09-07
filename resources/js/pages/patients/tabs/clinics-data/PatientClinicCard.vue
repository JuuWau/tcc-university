<script setup lang="ts">
import { computed } from 'vue';
import type { PatientForTab } from '@/types/patient/patient';
import { formatDateBr } from '@/src/utils/formatters';
import { FilePlus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
	patient: PatientForTab;
	activeStatus: 'waiting' | 'enrolled';
	canEnroll: boolean;
	canRemove: boolean;
}>();

const emit = defineEmits<{
	enroll: [patient: PatientForTab];
	remove: [patient: PatientForTab];
}>();

const isWaiting = computed(() => props.activeStatus === 'waiting');

const actionAllowed = computed(() =>
	isWaiting.value ? props.canEnroll : props.canRemove,
);

const statusLabel = computed(() =>
	isWaiting.value ? 'Lista de Espera' : 'Inscrito',
);

const statusClasses = computed(() =>
	isWaiting.value
		? 'bg-amber-100 text-amber-800'
		: 'bg-green-100 text-green-800',
);

const entryDate = computed(() => {
	if (!props.patient.created_at) {
		return '—';
	}

	return formatDateBr(props.patient.created_at);
});

const enrolledTimeLabel = computed(() => {
	const date =
		props.patient.joined_at ??
		props.patient.enrolled_at;

	if (!date) {
		return '—';
	}

	const startDate = new Date(date);
	const today = new Date();

	const diff =
		today.getTime() - startDate.getTime();

	const days = Math.floor(
		diff / (1000 * 60 * 60 * 24),
	);

	return `${days} ${days === 1 ? 'dia' : 'dias'}`;
});

function executeAction() {
	if (!actionAllowed.value) {
		return;
	}

	if (isWaiting.value) {
		emit('enroll', props.patient);
		return;
	}

	emit('remove', props.patient);
}
</script>

<template>
	<div
		class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
	>
		<div
			class="flex items-start justify-between gap-3"
		>
			<div class="min-w-0 flex-1">
				<p
					class="truncate text-sm font-semibold text-gray-900"
				>
					{{ patient.clinic_name }}
				</p>

				<p class="mt-1 text-xs text-gray-500">
					Data de entrada: {{ entryDate }}
				</p>
			</div>

			<span
				:class="[
					statusClasses,
					'shrink-0 rounded-full px-2.5 py-1 text-xs font-medium',
				]"
			>
				{{ statusLabel }}
			</span>
		</div>

		<div
			class="mt-4 flex items-end justify-between gap-4 border-t border-gray-100 pt-3"
		>
			<div>
				<p class="text-xs text-gray-500">
					{{ isWaiting ? 'Tempo na fila' : 'Tempo inscrito' }}
				</p>

				<p
					class="mt-1 text-sm font-medium text-gray-700"
				>
					{{ enrolledTimeLabel }}
				</p>
			</div>

			<button
				v-if="actionAllowed"
				type="button"
				class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
				:class="
					isWaiting
						? 'bg-green-50 text-green-700 hover:bg-green-100'
						: 'bg-red-50 text-red-700 hover:bg-red-100'
				"
				@click="executeAction"
			>
				<FilePlus
					v-if="isWaiting"
					class="h-4 w-4"
				/>

				<Trash2
					v-else
					class="h-4 w-4"
				/>

				{{ isWaiting ? 'Inscrever' : 'Remover' }}
			</button>
		</div>
	</div>
</template>