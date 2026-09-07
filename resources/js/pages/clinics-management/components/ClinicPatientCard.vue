<script setup lang="ts">
import type { PatientForTab } from '@/types/patient/patient';
import { FilePlus2, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    patient: PatientForTab;
    activeStatus: 'waiting' | 'enrolled';
    isAllowedToEnroll: boolean;
    isAllowedToRemove: boolean;
}>();

const emit = defineEmits<{
    enroll: [patient: PatientForTab];
    remove: [patient: PatientForTab];
}>();

const isWaiting = computed(() => props.activeStatus === 'waiting');

const actionAllowed = computed(() =>
    isWaiting.value ? props.isAllowedToEnroll : props.isAllowedToRemove,
);

const enrolledTimeLabel = computed(() => {
    if (!props.patient.enrolled_at) {
        return '—';
    }

    const enrolled = new Date(props.patient.enrolled_at);
    const today = new Date();

    const diff = today.getTime() - enrolled.getTime();

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    return `${days} ${days === 1 ? 'dia' : 'dias'}`;
});

function executeAction() {
    if (!actionAllowed.value) return;

    if (isWaiting.value) {
        emit('enroll', props.patient);
        return;
    }

    emit('remove', props.patient);
}
</script>

<template>
    <div
        class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">
                    {{ patient.name }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Código: {{ patient.code }}
                </p>
            </div>

            <span
                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium"
                :class="
                    isWaiting
                        ? 'bg-amber-100 text-amber-700'
                        : 'bg-green-100 text-green-700'
                "
            >
                {{ isWaiting ? 'Lista de Espera' : 'Inscrito' }}
            </span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-3">
            <div>
                <p class="text-xs text-gray-500">
                    {{ isWaiting ? 'Tempo na fila' : 'Tempo inscrito' }}
                </p>

                <p class="mt-1 text-sm font-medium text-gray-700">
                    {{ enrolledTimeLabel }}
                </p>
            </div>

            <div class="flex items-end justify-end">
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
                    <FilePlus2
                        v-if="isWaiting"
                        class="h-4 w-4 "
                    />

                        <Trash2
                        v-else
                        class="h-4 w-4 "
                        />

                    {{ isWaiting ? 'Inscrever' : 'Remover' }}
                </button>
            </div>
        </div>
    </div>
</template>
