import axios from 'axios';
import { computed, ref, watch } from 'vue';
import type { PatientScheduleBookingAppointment, PatientScheduleBookingDay, PatientScheduleBookingOption,PatientScheduleBookingTime, } from '@/types/patient/patientScheduleBooking';
import { toast } from 'vue3-toastify';

export function usePatientScheduleBooking(patientId: number, patientName: string,) {
    const clinics = ref<PatientScheduleBookingOption[]>([]);
    const periods = ref<PatientScheduleBookingOption[]>([]);
    const students = ref<PatientScheduleBookingOption[]>([]);

    const clinicId = ref<number | null>(null);
    const periodId = ref<number | null>(null);
    const studentId = ref<number | null>(null);
    const duration = ref<number | null>(null);

    const selectedDate = ref<string | null>(null);
    const selectedTime = ref<string | null>(null);

    const canShowCalendar = computed(() => {
        return (
            clinicId.value !== null &&
            periodId.value !== null &&
            studentId.value !== null
        );
    });

    const availableDays = ref<PatientScheduleBookingDay[]>([]);
    const availableTimes = ref<PatientScheduleBookingTime[]>([]);
    const appointments = ref<PatientScheduleBookingAppointment[]>([]);

    const loadingClinics = ref(false);
    const loadingPeriods = ref(false);
    const loadingStudents = ref(false);
    const loadingAvailability = ref(false);

    const currentMonth = ref(new Date());

    const procedureOptions = ref<PatientScheduleBookingOption[]>([]);

    const monthDays = computed(() => {
        const year = currentMonth.value.getFullYear();
        const month = currentMonth.value.getMonth();

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        const days: {
            date: string;
            day: number;
        }[] = [];
        const firstWeekDay = firstDay.getDay();

        for (let i = 0; i < firstWeekDay; i++) {
            days.push({
                date: '',
                day: 0,
            });
        }

        for (let day = 1; day <= lastDay.getDate(); day++) {
            const date = new Date(year, month, day);

            const formattedDate = [
                date.getFullYear(),
                String(date.getMonth() + 1).padStart(2, '0'),
                String(date.getDate()).padStart(2, '0'),
            ].join('-');

            days.push({
                date: formattedDate,
                day,
            });
        }

        return days;
    });

    const monthLabel = computed(() => {
        return currentMonth.value.toLocaleDateString('pt-BR', {
            month: 'long',
            year: 'numeric',
        });
    });

    const durations: PatientScheduleBookingOption[] = [
        {
            value: 15,
            label: '15 minutos',
        },
        {
            value: 20,
            label: '20 minutos',
        },
        {
            value: 30,
            label: '30 minutos',
        },
        {
            value: 40,
            label: '40 minutos',
        },
        {
            value: 60,
            label: '60 minutos',
        },
    ];

    const canLoadAvailability = computed(() => {
        return (
            clinicId.value !== null &&
            periodId.value !== null &&
            studentId.value !== null &&
            duration.value !== null
        );
    });

    const canConfirm = computed(() => {
        return (
            canLoadAvailability.value &&
            selectedDate.value !== null &&
            selectedTime.value !== null
        );
    });

    const hasSelectedFilters = computed(() => {
        return (
            clinicId.value !== null &&
            periodId.value !== null &&
            studentId.value !== null
        );
    });

    async function loadClinics() {
        loadingClinics.value = true;

        try {
            const { data } = await axios.get(
                `/patients/schedule/${patientId}/clinics`,
            );

            clinics.value = data.data ?? data;
        } finally {
            loadingClinics.value = false;
        }
    }

    async function loadPeriods() {
        if (!clinicId.value) {
            return;
        }

        loadingPeriods.value = true;

        try {
            const { data } = await axios.get(
                `/patients/schedule/${clinicId.value}/periods`,
            );

            periods.value = data.data ?? data;
        } finally {
            loadingPeriods.value = false;
        }
    }

    async function loadStudents() {
        if (!clinicId.value || !periodId.value) {
            return;
        }

        loadingStudents.value = true;

        try {
            const { data } = await axios.get(
                `/patients/schedule/${patientId}/students`,
                {
                    params: {
                        clinic_id: clinicId.value,
                        period_id: periodId.value,
                    },
                },
            );

            students.value = (data.data ?? data).map(
                (student: { id: number; name: string }) => ({
                    value: student.id,
                    label: student.name,
                }),
            );
        } finally {
            loadingStudents.value = false;
        }
    }

    async function loadAvailableDays() {
        if (!clinicId.value || !periodId.value || !studentId.value) 
            {
            return;
        }

        loadingAvailability.value = true;

        try {
            console.log(studentId.value);
            const { data } = await axios.get(
                `/patient-calendar/${patientId}/available-days`,
                {
                    params: {
                        clinic_id: clinicId.value,
                        period_id: periodId.value,
                        student_id: studentId.value,
                        month: currentMonth.value.getMonth() + 1,
                        year: currentMonth.value.getFullYear()
                    },
                },
            );

            availableDays.value = (data.available_days ?? []).map(
                (date: string) => ({
                    date,
                    label: new Date(
                        `${date}T00:00:00`,
                    ).toLocaleDateString('pt-BR', {
                        weekday: 'long',
                        day: '2-digit',
                        month: '2-digit',
                    }),
                }),
            );
        } finally {
            loadingAvailability.value = false;
        }
    }

    async function loadAvailableTimes() {
        if (!clinicId.value || !periodId.value || !studentId.value || !selectedDate.value) 
        {
            return;
        }

        loadingAvailability.value = true;

        try {
            const { data } = await axios.get(
                `/patient-calendar/${patientId}/available-times`,
                {
                    params: {
                        clinic_id: clinicId.value,
                        period_id: periodId.value,
                        student_id: studentId.value,
                        date: selectedDate.value,
                    },
                },
            );

            availableTimes.value = data.available_times ?? [];
            appointments.value = data.appointments ?? [];
        } finally {
            loadingAvailability.value = false;
        }
    }

    async function loadProcedures() {
        if (!clinicId.value) {
            procedureOptions.value = [];
            return;
        }

        const response = await axios.get('/procedures/list', {
            params: {
                clinic_id: clinicId.value,
            },
        });

        procedureOptions.value = response.data.procedures.map(
            (procedure: any) => ({
                label: `${procedure.name} (${procedure.specialty.name})`,
                value: procedure.id,
            }),
        );
    }

    async function previousMonth() {
        currentMonth.value = new Date(
            currentMonth.value.getFullYear(),
            currentMonth.value.getMonth() - 1,
            1,
        );

        if (hasSelectedFilters.value) {
            await loadAvailableDays();
        }
    }

    async function nextMonth() {
        currentMonth.value = new Date(
            currentMonth.value.getFullYear(),
            currentMonth.value.getMonth() + 1,
            1,
        );

        if (hasSelectedFilters.value) {
            await loadAvailableDays();
        }
    }

    async function selectDate(date: string) {
        if (!date) 
        {
            return;
        }

        selectedDate.value = date;
        selectedTime.value = null;
        availableTimes.value = [];

        await loadAvailableTimes();
    }

    async function updateAppointmentTime(appointmentId: number, scheduledStartAt: string, scheduledEndAt: string,) 
    {
        try {
            await axios.patch(
                `/patient-calendar/${patientId}/${appointmentId}/time`,
                {
                    scheduled_start_at: scheduledStartAt,
                    scheduled_end_at: scheduledEndAt,
                },
            );

            await loadAvailableTimes();

            toast.success('Horário do agendamento atualizado com sucesso.');
        } catch (error: any) {
            toast.error(
                error.response?.data?.message ??
                    'Não foi possível atualizar o horário do agendamento.',
            );

            throw error;
        }
    }

    function resetAvailability() {
        availableDays.value = [];
        availableTimes.value = [];
        appointments.value = [];
        selectedDate.value = null;
        selectedTime.value = null;
    }

    function resetBooking() {
        clinicId.value = null;
        periodId.value = null;
        studentId.value = null;
        duration.value = null;

        periods.value = [];
        students.value = [];

        resetAvailability();

        currentMonth.value = new Date();
    }

    watch(clinicId, async () => {
        periods.value = [];
        students.value = [];

        periodId.value = null;
        studentId.value = null;

        resetAvailability();

        if (clinicId.value) {
            await loadPeriods();
            await loadProcedures();
        }
    });

    watch(periodId, async () => {
        students.value = [];

        studentId.value = null;

        resetAvailability();

        if (periodId.value) {
            await loadStudents();
        }
    });

    watch(studentId, async () => {
        resetAvailability();

        if (studentId.value) {
            await loadAvailableDays();
        }
    });

    return {
        clinics,
        periods,
        students,
        durations,

        clinicId,
        periodId,
        studentId,
        procedureOptions,
        patientId,
        patientName,

        currentMonth,
        monthDays,
        monthLabel,

        selectedDate,
        selectedTime,

        availableDays,
        availableTimes,
        appointments,

        loadingClinics,
        loadingPeriods,
        loadingStudents,
        loadingAvailability,

        canLoadAvailability,
        canConfirm,
        hasSelectedFilters,
        canShowCalendar,

        loadClinics,
        loadPeriods,
        loadStudents,
        loadAvailableTimes,
        loadProcedures,
        updateAppointmentTime,

        previousMonth,
        nextMonth,
        selectDate,

        resetAvailability,
        resetBooking,
    };
}