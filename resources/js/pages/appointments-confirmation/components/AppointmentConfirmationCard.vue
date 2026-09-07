<script setup lang="ts">
import StatusBadgeAppointment from '@/components/badges/StatusBadgeAppointment.vue';
import { formatDateBr } from '@/src/utils/formatters';
import { CalendarPlus, Check, MessageCircle, X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    appointment: any;
}>();

const emit = defineEmits<{
    confirm: [appointment: any];
    cancel: [appointment: any];
    schedule: [appointment: any];
    whatsapp: [appointment: any];
}>();

const startDate = computed(() => {
    if (!props.appointment.scheduled_start_at) {
        return '—';
    }

    return formatDateBr(props.appointment.scheduled_start_at);
});

const startTime = computed(() => {
    if (!props.appointment.scheduled_start_at) {
        return '—';
    }

    return props.appointment.scheduled_start_at.substring(11, 16);
});

const endTime = computed(() => {
    if (!props.appointment.scheduled_end_at) {
        return '—';
    }

    return props.appointment.scheduled_end_at.substring(11, 16);
});

const whatsappMessage = computed(() => {
    const patient = props.appointment.patient;

    return `Olá ${patient?.name} - ${patient?.code},

Seu atendimento está agendado para ${startDate.value} às ${startTime.value}.

Podemos confirmar sua presença?`;
});

const hasPhone = computed(() => Boolean(props.appointment.patient?.phone));

function openWhatsapp() {
    if (!hasPhone.value) {
        return;
    }

    emit('whatsapp', props.appointment);
}
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">
                    {{ appointment.patient?.name ?? '—' }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    {{ appointment.patient?.code ?? '—' }}
                </p>
            </div>

            <StatusBadgeAppointment :params="{ value: appointment.status }" />
        </div>

        <div class="mt-4 space-y-3 border-t border-gray-100 pt-3">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500">Data</p>

                    <p class="mt-1 text-sm font-medium text-gray-700">
                        {{ startDate }}
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-500">Horário</p>

                    <p class="mt-1 text-sm font-medium text-gray-700">
                        {{ startTime }} às {{ endTime }}
                    </p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-500">Aluno</p>

                <p class="mt-1 text-sm font-medium text-gray-700">
                    {{ appointment.student?.name ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Clínica</p>

                <p class="mt-1 text-sm font-medium text-gray-700">
                    {{ appointment.clinic?.name ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Período</p>

                <p class="mt-1 text-sm font-medium text-gray-700">
                    {{ appointment.period?.name ?? '—' }}
                </p>
            </div>
        </div>

        <div
            class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 pt-3"
        >
            <button
                v-if="hasPhone"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 transition hover:bg-green-100"
                @click="openWhatsapp"
            >
                <MessageCircle class="h-4 w-4" /> WhatsApp
            </button>
            <template v-if="appointment.status === 'scheduled'">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 transition hover:bg-green-100"
                    @click="emit('confirm', appointment)"
                >
                    <Check class="h-4 w-4" /> Confirmar
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100"
                    @click="emit('cancel', appointment)"
                >
                    <X class="h-4 w-4" /> Cancelar
                </button>
            </template>
            <template v-else-if="appointment.status === 'confirmed'">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100"
                    @click="emit('cancel', appointment)"
                >
                    <X class="h-4 w-4" /> Cancelar
                </button>
            </template>
            <template v-else-if="appointment.status === 'canceled'">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-sky-50 px-3 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-100"
                    @click="emit('schedule', appointment)"
                >
                    <CalendarPlus class="h-4 w-4" /> Agendar
                </button>
            </template>
        </div>
    </div>
</template>
