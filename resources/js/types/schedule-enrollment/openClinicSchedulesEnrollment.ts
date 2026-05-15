/** Linha da grade de agendas abertas por clínica (OpenClinicSchedules). */
export interface OpenClinicScheduleEnrollmentRow {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    available_slots: number;
    period_id: number;
    responsible_id: number;
    period_label: string;
    responsible_name: string;
    allow_student_booking: boolean;
    is_enrolled: boolean;
}

export interface OpenClinicScheduleEnrollmentClinic {
    id: number;
    name: string;
}

export interface OpenClinicSchedulesEnrollmentFilters {
    date: string | null;
}
