export interface ClinicReport {
    id: number;
    university_id: number;
    name: string;
    active: boolean;
    schedule_slots_count: number;
    available_slots_sum: number;
    created_at: string | null;
}

export interface ClinicReportPeriod {
    id: number;
    name: string;
}

export interface ClinicReportSummary {
    total: number;
    active: number;
    inactive: number;
    with_schedule: number;
    without_schedule: number;
}

export interface ClinicReportFilters {
        search: string;
        period_id: number | null;
        status: string | null;
}

export interface ClinicReportResponse {
    clinics: {
        data: ClinicReport[];
        current_page: number;
        last_page: number;
        total: number;
    };
    summary: ClinicReportSummary;
}
