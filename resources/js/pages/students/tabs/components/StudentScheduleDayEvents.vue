<script setup lang="ts">
import FullCalendar from '@fullcalendar/vue3';

import ptBrLocale from '@fullcalendar/core/locales/pt-br';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

import {
    StudentScheduleContextKey,
    type StudentScheduleContext,
} from '@/keys/students/studentScheduleKeys';

import {
    formatDateBr,
    formatDateTime,
    isPastDate,
} from '@/src/utils/formatters';
import { computed, inject, watch } from 'vue';

const schedule = inject(StudentScheduleContextKey) as StudentScheduleContext;

const emit = defineEmits<{
    (e: 'edit', appointment: any): void;

    (
        e: 'create',
        payload: {
            date: string;
            start_time: string;
            end_time: string;
        },
    ): void;
}>();

if (!schedule) {
    throw new Error(
        'StudentScheduleDayEvents must be used inside StudentScheduleTab',
    );
}

const selectedDate = computed(() => schedule.selectedDate?.value);

const slotStartTime = computed(() => schedule.slotStartTime?.value);

const slotEndTime = computed(() => schedule.slotEndTime?.value);

const canCreateAppointments = computed(
    () => !!selectedDate.value && !isPastDate(selectedDate.value),
);

function getAppointmentClassName(status: string) {
    return `appointment-${status}`;
}

watch(
    () => [schedule.slotStartTime?.value, schedule.slotEndTime?.value],
    ([start, end]) => {
        console.log('slot mudou', start, end);
    }
);

const statusLegend = [
    {
        label: 'Agendado',
        color: 'bg-sky-200',
    },
    {
        label: 'Confirmado',
        color: 'bg-lime-300',
    },
    {
        label: 'Concluído',
        color: 'bg-teal-300',
    },
    {
        label: 'Cancelado',
        color: 'bg-slate-300',
    },
    {
        label: 'Não compareceu',
        color: 'bg-red-300',
    },
    {
        label: 'Remarcado',
        color: 'bg-amber-400',
    },
];

const blockedEvents = computed(() => {
    if (!selectedDate.value || !slotStartTime.value || !slotEndTime.value) 
    {
        return [];
    }

    return [
        {
            start: `${selectedDate.value}T00:00:00`,
            end: `${selectedDate.value}T${slotStartTime.value}:00`,
            display: 'background',
            className: 'blocked-time',
        },
        {
            start: `${selectedDate.value}T${slotEndTime.value}:00`,
            end: `${selectedDate.value}T23:59:59`,
            display: 'background',
            className: 'blocked-time',
        },
    ];
});

const calendarEvents = computed(() => {
    return schedule.filteredDayEvents.value.map((event) => ({
        id: String(event.id),

        title: event.patient,

        start: `${event.date}T${event.start_time}:00`,

        end: `${event.date}T${event.end_time}:00`,

        className: getAppointmentClassName(event.status),

        extendedProps: {
            status: event.status,
            notes: event.notes,
            appointment: event,
        },
    }));
});

function handleEventClick(info: any) {
    emit('edit', info.event.extendedProps.appointment);
}

function handleDateSelect(info: any) {
    const date = info.startStr.split('T')[0];

    if (isPastDate(date)) {
        return;
    }

    emit('create', {
        date,
        start_time: formatDateTime(info.start).slice(11, 16),
        end_time: formatDateTime(info.end).slice(11, 16),
    });
}

async function saveEventTime(info: any) {
    const event = info.event;

    try {
        await schedule.updateAppointmentTime(
            Number(event.id),
            formatDateTime(event.start),
            formatDateTime(event.end),
        );
    } catch {
        info.revert();
    }
}

function isInsideAllowedRange(start: Date, end: Date) {
    if (!slotStartTime.value || !slotEndTime.value) {
        return false;
    }

    const selectedStart = formatDateTime(start).slice(11, 16);
    const selectedEnd = formatDateTime(end).slice(11, 16);

    return (
        selectedStart >= slotStartTime.value &&
        selectedEnd <= slotEndTime.value
    );
}

const calendarOptions = computed(() => ({
    plugins: [timeGridPlugin, interactionPlugin],

    initialView: 'timeGridDay',
    locale: ptBrLocale,

    allDaySlot: false,
    height: 'auto',

    selectable: canCreateAppointments.value,
    selectMirror: canCreateAppointments.value,

    selectAllow: (info: { start: Date; end: Date }) => {
        if (!canCreateAppointments.value) {
            return false;
        }
        console.log('selectAllow', info.start, info.end);

        return isInsideAllowedRange(info.start, info.end);
    },

    slotDuration: '00:05:00',
    snapDuration: '00:05:00',

    slotMinTime: '06:00:00',
    slotMaxTime: '23:00:00',

    headerToolbar: false,

    editable: true,

    eventClick: handleEventClick,
    select: handleDateSelect,
    eventDrop: saveEventTime,
    eventResize: saveEventTime,

    events: [...calendarEvents.value, ...blockedEvents.value],

    initialDate: selectedDate.value,

    eventAllow: (dropInfo) => {
        const newStart = dropInfo.start;
        const newEnd = dropInfo.end ?? dropInfo.start;

        return !blockedEvents.value.some(blocked => {
            if (!blocked.start || !blocked.end) {
                return false;
            }

            const blockedStart = new Date(blocked.start);
            const blockedEnd = new Date(blocked.end);

            return (
                newStart < blockedEnd &&
                newEnd > blockedStart
            );
        });
    },
}));
</script>

<template>
    <div
        v-if="selectedDate"
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    >
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Agenda do dia</h2>

            <p class="text-sm text-gray-500">
                {{ formatDateBr(selectedDate) }}
            </p>

            <p
                v-if="!canCreateAppointments"
                class="mt-1 text-sm text-amber-600"
            >
                Este dia já passou. Não é possível criar novos agendamentos.
            </p>
        </div>
        <div class="mb-4 flex flex-wrap gap-4">
            <div
                v-for="status in statusLegend"
                :key="status.label"
                class="flex items-center gap-2 text-sm text-gray-700"
            >
                <span class="h-3 w-3 rounded-full" :class="status.color" />

                <span>{{ status.label }}</span>
            </div>
        </div>
        <div class="max-h-[600px] overflow-y-auto">
            <FullCalendar :key="selectedDate" :options="calendarOptions" />
        </div>
    </div>

    <div
        v-else
        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500"
    >
        Selecione um dia para visualizar os agendamentos.
    </div>
</template>
