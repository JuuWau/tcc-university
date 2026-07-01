export interface OpenScheduleOption {
    id: number;
    label: string;
}

export interface OpenScheduleSlot {
    id: number;
    university_id: number;
    period_id: number;
    clinic_id: number;
    clinic_name: string | null;
    responsible_id: number;
    date: string;
    start_time: string;
    end_time: string;
    available_slots: number;
}

export interface OpenSchedulePayload {
    clinic_id: number;
    available_slots: number | null;
    allow_student_booking: boolean;
    allow_student_enrollment: boolean;
    allow_procedure_booking: boolean;
    period_id: number;
    responsible_id: number;
    days: string[];
    start_time: string;
    end_time: string;
}

export interface OpenScheduleConflict {
    clinic_id?: number;
    clinic_name: string;
    date: string;
    start_time: string;
    end_time: string;
    period_id?: number;
}

export interface OpenScheduleResponse {
    message: string;
    slots: OpenScheduleSlot[];
}

export interface OpenScheduleErrorResponse {
    message?: string;
    conflict?: OpenScheduleConflict | null;
}
