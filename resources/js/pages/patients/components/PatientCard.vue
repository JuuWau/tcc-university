<script setup lang="ts">
import type {
	PatientStatusKey,
	PatientWithInvite,
} from '@/types/patient/patient';
import { PATIENT_STATUS } from '@/types/patient/patient';
import { BadgeMinus, Eye, Trash2, UserCheck } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
	patient: PatientWithInvite;
	canUpdate: boolean;
	canDelete: boolean;
	canDeactivate: boolean;
}>();

const emit = defineEmits<{
	view: [patient: PatientWithInvite];
	deactivate: [patient: PatientWithInvite];
	activate: [patient: PatientWithInvite];
	delete: [patient: PatientWithInvite];
}>();

const status = computed(() => {
	if (props.patient.deleted_at) return 'Excluído';

	const key = (props.patient.status ?? 'ativo') as PatientStatusKey;

	return PATIENT_STATUS[key] ?? key;
});

const statusClasses: Record<string, string> = {
	Ativo: 'bg-green-200 text-green-800',
	Tratamento: 'bg-blue-100 text-blue-800',
	'Pausa no Tratamento': 'bg-amber-100 text-amber-800',
	Abandono: 'bg-red-100 text-red-800',
	Concluído: 'bg-emerald-100 text-emerald-800',
	Transferência: 'bg-violet-100 text-violet-800',
	Pendente: 'bg-yellow-100 text-yellow-800',
	Excluído: 'bg-gray-200 text-gray-600',
};

const isInativo = computed(() => props.patient.deleted_at !== null);

const canDeactivatePatient = computed(
	() => !isInativo.value && props.canDeactivate,
);
</script>

<template>
	<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
		<div class="min-w-0">
			<h3 class="break-words font-semibold text-gray-900">
				{{ patient.name }}
			</h3>

			<p class="mt-1 break-all text-sm text-gray-500">
				{{ patient.email }}
			</p>

			<p class="mt-1 text-sm text-gray-500">
				Código: {{ patient.code }}
			</p>

			<div
				v-if="patient.students?.length"
				class="mt-3 flex flex-wrap gap-1"
			>
				<span
					v-for="student in patient.students"
					:key="student.id"
					class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700"
				>
					{{ student.name }}
				</span>
			</div>
		</div>

		<div
			class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3"
		>
			<span
				class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-xs font-semibold"
				:class="
					statusClasses[status] ?? 'bg-gray-100 text-gray-800'
				"
			>
				{{ status }}
			</span>

			<div class="flex items-center gap-4">
				<Eye
					class="cursor-pointer text-blue-600 hover:text-blue-800"
					:size="20"
					title="Visualizar cadastro"
					@click="emit('view', patient)"
				/>

				<BadgeMinus
					v-if="canDeactivatePatient"
					class="cursor-pointer text-yellow-600 hover:text-yellow-800"
					:size="20"
					title="Inativar paciente"
					@click="emit('deactivate', patient)"
				/>

				<UserCheck
					v-if="isInativo && canUpdate"
					class="cursor-pointer text-green-600 hover:text-green-800"
					:size="20"
					title="Ativar paciente"
					@click="emit('activate', patient)"
				/>

				<Trash2
					v-if="isInativo && canDelete"
					class="cursor-pointer text-red-500 hover:text-red-700"
					:size="20"
					title="Excluir paciente"
					@click="emit('delete', patient)"
				/>
			</div>
		</div>
	</div>
</template>
