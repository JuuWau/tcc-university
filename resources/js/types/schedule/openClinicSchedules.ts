/** Linha da grade de agendas abertas por clínica (OpenClinicSchedules). */
export interface OpenClinicScheduleRow {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    available_slots: number;
    period_id: number;
    responsible_id: number;
    period_label: string;
    responsible_name: string;
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
