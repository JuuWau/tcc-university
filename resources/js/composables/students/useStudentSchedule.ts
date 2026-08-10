import type {
    ClinicOption,
    MonthData,
    MonthDay,
    ScheduleEvent,
} from '@/types/student/studentSchedule';
import axios from 'axios';

import { computed, onMounted, ref, watch, inject } from 'vue';

export function useStudentSchedule(studentId: number) {
    const clinics = ref<ClinicOption[]>([]);
    const selectedClinic = ref<number | null>(null);
    const current = ref(new Date());
    const selectedDate = ref<string | null>(null);
    const availableDays = ref<string[]>([]);
    const events = ref<ScheduleEvent[]>([]);
    const loadingEvents = ref(false);
    const slotStartTime = ref<string | null>(null);
    const slotEndTime = ref<string | null>(null);
    const openDays = ref<string[]>([]);
    const patientOptions = ref([]);
    const allowProcedureBooking = ref<boolean | null>(null);
    const procedureOptions = ref([]);
    const scheduleEnrollmentId = ref<number | null>(null);
    const calendarEvents = ref<ScheduleEvent[]>([]);
    const dayEvents = ref<ScheduleEvent[]>([]);
    const calendarKey = ref(0);

    async function fetchPatients() {
        const response = await axios.get(`/students/${studentId}/patients`);

        patientOptions.value = response.data.data;
    }
    
    async function fetchClinics() {
        const response = await axios.get(
            `/students/${studentId}/clinics`,
        );

        clinics.value = response.data.data;

        selectedClinic.value = null;
    }

    async function fetchSchedule() {
        if (!selectedClinic.value) {
            return;
        }

        const response = await axios.get(
            `/students/${studentId}/schedule`,
            {
                params: {
                    clinic_id: selectedClinic.value,
                },
            },
        );

        openDays.value = response.data.open_days;
        calendarEvents.value = response.data.events;
        
        reloadCalendar();
    }

    async function fetchProcedures() {
        const response = await axios.get('/procedures/list', {
            params: {
                clinic_id: selectedClinic.value,
            },
        });

        procedureOptions.value = response.data.procedures.map(
            (procedure: any) => ({
                label: `${procedure.name} (${procedure.specialty.name})`,
                value: procedure.id,
            }),
        );
    }

    async function fetchDayEvents(date: string) {
        loadingEvents.value = true;
        

        try {
            const response = await axios.get(
                `/student-calendar/${studentId}/appointments`,
                {
                    params: {
                        clinic_id: selectedClinic.value,
                        date,
                    },
                },
            );

            dayEvents.value = response.data.appointments;

            slotStartTime.value = response.data.slot.start_time;

            slotEndTime.value = response.data.slot.end_time;

            allowProcedureBooking.value = response.data.slot.allow_procedure_booking;

            scheduleEnrollmentId.value = response.data.schedule_enrollment_id;

        } finally {
            loadingEvents.value = false;
        }
    }

    async function selectDate(date: string) {
        selectedDate.value = date;

        await fetchDayEvents(date);
    }

    async function updateAppointmentTime(
        appointmentId: number,
        scheduledStartAt: string,
        scheduledEndAt: string,
    ) {
        const response = await axios.patch(
            `/student-calendar/${appointmentId}/time`,
            {
                scheduled_start_at: scheduledStartAt,
                scheduled_end_at: scheduledEndAt,
            },
        );

        const updatedAppointment = response.data.data;

        const index = events.value.findIndex(
            event => event.id === updatedAppointment.id,
        );

        if (index !== -1) {
            events.value[index] = updatedAppointment;
        }

        return updatedAppointment;
    }

    function reloadCalendar() {
        calendarKey.value++;
    } 

    watch(selectedClinic, async () => {
        selectedDate.value = null;

        await Promise.all([
            fetchSchedule(),
            fetchProcedures(),
        ]);
    });

    onMounted(async () => {
        await Promise.all([
            fetchClinics(),
            fetchPatients(),
            fetchProcedures(),
        ]);
    });

    function nextMonths() {
        current.value.setMonth(current.value.getMonth() + 3);

        current.value = new Date(current.value);
    }

    function prevMonths() {
        current.value.setMonth(current.value.getMonth() - 3);

        current.value = new Date(current.value);
    }

    async function selectDay(day: MonthDay) {
        if (!openDays.value.includes(day.date)) {
            return;
        }

        selectedDate.value = day.date;

        await fetchDayEvents(day.date);
    }

    const filteredDayEvents = computed(() => {
        return dayEvents.value;
    });

    function generateMonth(baseDate: Date): MonthData {
        const year = baseDate.getFullYear();
        const month = baseDate.getMonth();

        const daysInMonth = new Date(
                year,
                month + 1,
                0,
        ).getDate();

        const firstDayWeek = new Date(
                year,
                month,
                1,
        ).getDay();

        const days: MonthDay[] = [];

        for (let i = 0; i < firstDayWeek; i++) {
                days.push({
                day: 0,
                date: `empty-${month}-${i}`,
                });
        }

        for (let i = 1; i <= daysInMonth; i++) {
                const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

                days.push({
                day: i,
                date,
                });
        }

        return {
                month,
                label: baseDate.toLocaleDateString('pt-BR', {
                month: 'long',
                year: 'numeric',
                }),
                days,
        };
    }

    function hasEvent(date: string): boolean {
        return calendarEvents.value.some(event => event.date === date);
    }

    const visibleMonths = computed<MonthData[]>(() => {
        const base = current.value ?? new Date();

        return [0, 1, 2].map((i) => {
                const d = new Date(base);
                d.setMonth(d.getMonth() + i);

                return generateMonth(d);
        });
        });

    return {
        clinics,
        current,
        selectedClinic,
        selectedDate,
        visibleMonths,
        openDays,
        patientOptions,
        procedureOptions,
        allowProcedureBooking,
        filteredDayEvents,
        studentId,
        scheduleEnrollmentId,
        calendarKey,
        slotStartTime,
        slotEndTime,
        nextMonths,
        prevMonths, 
        selectDay,
        fetchClinics,
        selectDate,
        reloadCalendar,
        hasEvent,
        updateAppointmentTime,
        fetchPatients,
        fetchProcedures
        };
}