
export interface OpenClinicScheduleRow {
    id: number;
    clinic_id: number;
    clinic_name: string;
    date: string;
    start_time: string;
    end_time: string;
    available_slots: number;
    period_id: number;
    responsible_id: number;
    period_label: string;
    responsible_name: string;
    enrolled_students_count?: number;
    allow_student_booking: boolean;
    allow_student_enrollment: boolean;
    allow_procedure_booking: boolean;
}

export interface OpenClinicScheduleResponsibleOption {
    id: number;
    label: string;
}

export interface OpenClinicScheduleClinic {
    id: number;
    name: string;
}

export interface OpenClinicSchedulePeriodOption {
    id: number;
    label: string;
}

export interface OpenClinicSchedulesFilters {
    period_id: number | null;
    date: string | null;
}
