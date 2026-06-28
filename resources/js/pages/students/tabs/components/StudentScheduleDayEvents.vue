<script setup lang="ts">
import FullCalendar from '@fullcalendar/vue3';

import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';

import {
    StudentScheduleContextKey,
    type StudentScheduleContext,
} from '@/keys/students/studentScheduleKeys';

import { computed, inject } from 'vue';

const schedule = inject(
    StudentScheduleContextKey,
) as StudentScheduleContext;

if (!schedule) {
    throw new Error(
        'StudentScheduleDayEvents must be used inside StudentScheduleTab',
    );
}

const selectedDate = computed(
    () => schedule.selectedDate?.value,
);

const calendarEvents = computed(() => {
    return schedule.dayEvents.value.map((event) => ({
        id: String(event.id),

        title: `${event.patient} - ${event.procedure}`,

        start: `${event.date}T${event.time}:00`,
    }));
});

function handleEventClick(info: any) {
    const eventId = info.event.id;

    console.log('abrir modal', eventId);
}

const calendarOptions = computed(() => ({
    plugins: [
        timeGridPlugin,
        interactionPlugin,
    ],

    initialView: 'timeGridDay',

    locale: ptBrLocale,

    allDaySlot: false,

    height: 'auto',

    slotMinTime: '07:00:00',
    slotMaxTime: '23:00:00',

    headerToolbar: false,

    events: calendarEvents.value,

    initialDate: selectedDate.value,

    eventClick: handleEventClick,
}));
</script>

<template>
    <div
        v-if="selectedDate"
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    >
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                Agenda do dia
            </h2>

            <p class="text-sm text-gray-500">
                {{ selectedDate }}
            </p>
        </div>

        <FullCalendar :options="calendarOptions" />
    </div>

    <div
        v-else
        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500"
    >
        Selecione um dia para visualizar os agendamentos.
    </div>
</template>