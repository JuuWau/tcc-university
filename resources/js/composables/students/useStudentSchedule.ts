import type {
    ClinicOption,
    MonthData,
    MonthDay,
    ScheduleEvent,
} from '@/types/student/studentSchedule';
import axios from 'axios';

import { computed, onMounted, ref, watch } from 'vue';

export function useStudentSchedule(studentId: number) {
    const clinics = ref<ClinicOption[]>([]);
    const selectedClinic = ref<number | null>(null);
    const current = ref(new Date());
    const selectedDate = ref<string | null>(null);
    const availableDays = ref<string[]>([]);
    const events = ref<ScheduleEvent[]>([
        {
            id: 1,
            date: '2026-04-07',
            time: '14:00',
            procedure: 'Limpeza',
            patient: 'Maria',
        },
    ]);

    const openDays = ref<string[]>([]);
    
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
        events.value = response.data.events;
    }

    watch(selectedClinic, async () => {
        selectedDate.value = null;
        await fetchSchedule();
    });

    watch(selectedClinic, (v) => {
    console.log('selectedClinic mudou:', v);
});

    onMounted(async () => {
        await fetchClinics();
    });

    function nextMonths() {
        current.value.setMonth(current.value.getMonth() + 3);

        current.value = new Date(current.value);
    }

    function prevMonths() {
        current.value.setMonth(current.value.getMonth() - 3);

        current.value = new Date(current.value);
    }

    function selectDay(day: MonthDay) {
        if (!openDays.value.includes(day.date)) {
            return;
        }

        selectedDate.value = day.date;
    }

    const dayEvents = computed<ScheduleEvent[]>(() => {
        if (!selectedDate.value) {
            return [];
        }

        return events.value.filter(
            (event) => event.date === selectedDate.value,
        );
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
        dayEvents,
        openDays,

        nextMonths,
        prevMonths,
        selectDay,
        fetchClinics,
        };
}