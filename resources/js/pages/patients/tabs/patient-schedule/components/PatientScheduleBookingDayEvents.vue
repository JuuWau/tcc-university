<script setup lang="ts">
import FullCalendar from '@fullcalendar/vue3';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import { PatientScheduleBookingContextKey, } from '@/keys/patients/patientScheduleBookingKeys';
import { formatDateBr, formatDateTime, isPastDate,} from '@/src/utils/formatters';
import { computed, inject } from 'vue';
import type { PatientScheduleBookingAppointment, PatientScheduleCreateData, } from '@/types/patient/patientScheduleBooking';

const booking = inject(PatientScheduleBookingContextKey);

if (!booking) {
    throw new Error(
        'PatientScheduleBookingDayEvents deve ser usado dentro de PatientScheduleBooking.',
    );
}

const emit = defineEmits<{
    (
        e: 'view',
        appointment: PatientScheduleBookingAppointment,
    ): void;

    (
        e: 'create',
        data: PatientScheduleCreateData,
    ): void;
}>();

const selectedDate = computed(
    () => booking.selectedDate.value,
);

const availableTimes = booking.availableTimes;

const appointments = booking.appointments;

const canCreateAppointments = computed(
    () =>
        !!selectedDate.value &&
        !isPastDate(selectedDate.value),
);

const formattedDate = computed(() =>
    selectedDate.value
        ? formatDateBr(selectedDate.value)
        : '',
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
    if (!selectedDate.value || availableTimes.value.length === 0) 
    {
        return [];
    }

    const startTime =
        availableTimes.value[0]?.start_time;

    const endTime =
        availableTimes.value[
            availableTimes.value.length - 1
        ]?.end_time;

    if (!startTime || !endTime) {
        return [];
    }

    return [
        {
            start: `${selectedDate.value}T06:00:00`,
            end: `${selectedDate.value}T${startTime}:00`,
            display: 'background',
            className: ['blocked-time'],
        },

        {
            start: `${selectedDate.value}T${endTime}:00`,
            end: `${selectedDate.value}T23:00:00`,
            display: 'background',
            className: ['blocked-time'],
        },
    ];
});

const calendarEvents = computed(() => {
    return appointments.value.map((appointment) => ({
        id: String(appointment.id),

        title: appointment.patient ?? 'Paciente',

        start: `${appointment.date}T${appointment.start_time}:00`,

        end: `${appointment.date}T${appointment.end_time}:00`,

        className: getAppointmentClassName(
            appointment.status,
        ),

        editable: canEditAppointment(appointment),

        startEditable: canEditAppointment(appointment),

        durationEditable: canEditAppointment(appointment),

        extendedProps: {
            status: appointment.status,
            notes: appointment.notes,
            appointment,
        },
    }));
});

function getAppointmentClassName(status: string) {
    return `appointment-${status}`;
}

function canEditAppointment( appointment: PatientScheduleBookingAppointment,) 
{
    return appointment.patient_id === booking.patientId;
}

function handleEventClick(info: any) {
    emit(
        'view',
        info.event.extendedProps.appointment,
    );
}

function handleDateSelect(info: any) {
    const date = info.startStr.split('T')[0];

    if (isPastDate(date)) {
        return;
    }

    const allowedTime = getAllowedTime(
        info.start,
        info.end,
    );

    if (!allowedTime) {
        return;
    }

    emit('create', {
        schedule_enrollment_id:
            allowedTime.schedule_enrollment_id,

        date,

        start_time:
            formatDateTime(info.start).slice(11, 16),

        end_time:
            formatDateTime(info.end).slice(11, 16),

        allow_procedure_booking:
            allowedTime.allow_procedure_booking,

        patient_id: booking.patientId,

        patient: booking.patientName,

        status: 'scheduled',

        notes: '',
    });
}

function getAllowedTime(start: Date, end: Date) {
    const selectedStart =
        formatDateTime(start).slice(11, 16);

    const selectedEnd =
        formatDateTime(end).slice(11, 16);

    return availableTimes.value.find((time) => {
        return (
            selectedStart >= time.start_time &&
            selectedEnd <= time.end_time
        );
    });
}

function isInsideAllowedRange(start: Date, end: Date) 
{
    if (availableTimes.value.length === 0) {
        return false;
    }

    const selectedStart =
        formatDateTime(start).slice(11, 16);

    const selectedEnd =
        formatDateTime(end).slice(11, 16);

    return availableTimes.value.some((time) => {
        return (
            selectedStart >= time.start_time &&
            selectedEnd <= time.end_time
        );
    });
}

async function saveEventTime(info: any) {
    const event = info.event;

    const appointment = event.extendedProps.appointment as PatientScheduleBookingAppointment;

    if (!canEditAppointment(appointment)) {
        info.revert();
        return;
    }

    if (!event.start || !event.end) {
        info.revert();
        return;
    }

    const date = formatDateTime(event.start).slice(0, 10);

    if (isPastDate(date)) 
    {
        info.revert();
        return;
    }

    if (!isInsideAllowedRange(event.start, event.end)) 
    {
        info.revert();
        return;
    }

    try { 
        await booking.updateAppointmentTime(
            Number(event.id),
            formatDateTime(event.start),
            formatDateTime(event.end),
        );
    } catch {
        info.revert();
    }
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

    selectable: canCreateAppointments.value,

    selectMirror: canCreateAppointments.value,

    selectAllow: (info: {
        start: Date;
        end: Date;
    }) => {
        if (!canCreateAppointments.value) {
            return false;
        }

        return isInsideAllowedRange(
            info.start,
            info.end,
        );
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

    events: [
        ...calendarEvents.value,
        ...blockedEvents.value,
    ],

    initialDate: selectedDate.value,
}));
</script>

<template>
    <div
        v-if="selectedDate"
        class="rounded-2xl border border-gray-200 bg-white p-5"
    >
        <div
            class="pb-4"
        >
            <h3
                class="text-base font-semibold capitalize text-gray-900"
            >
                Agenda do dia
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                {{ formattedDate }}
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

        <div class="max-h-[650px] overflow-y-auto">
            <FullCalendar
                :key="selectedDate"
                :options="calendarOptions"
            />
        </div>
    </div>

    <div
        v-else
        class="flex min-h-[500px] items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center"
    >
        <div>
            <p
                class="text-sm font-medium text-gray-600"
            >
                Selecione um dia
            </p>

            <p
                class="mt-1 text-sm text-gray-400"
            >
                Os horários disponíveis serão exibidos
                aqui.
            </p>
        </div>
    </div>
</template>