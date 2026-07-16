
import axios from 'axios';

export function useAttendance() {
    async function loadDates(clinicId: number,periodId: number,) 
    {
        const { data } = await axios.get(
            `/attendance/clinics/${clinicId}/dates`,
            {
                params: {
                    period_id: periodId,
                },
            },
        );

        return data.dates;
    }

    async function loadStudents(slotId: number)
    {
        const { data } = await axios.get(
            `/attendance/schedule-slots/${slotId}/students`,
        );

        return data.students;
    }

    async function updateAttendance(
        slotId: number,
        students: {
            student_id: number;
            attended: boolean;
        }[]
    ) {
        const { data } = await axios.put(`/attendance/schedule-slots/${slotId}`, {
            students,
        });

        return data;
    }

    return {
        loadDates,
        loadStudents,
        updateAttendance,
    };
}