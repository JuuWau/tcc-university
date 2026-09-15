export interface StudentPerformanceReport {
    id: number;
    name: string;
    registration: string;
    period: string | null;
    status: string;
    attended: string;
    created_at: string;
}

export interface StudentPerformanceReportSummary {
    total: number;
    active: number;
    inactive: number;
    invitation_accepted: number;
    invitation_pending: number;
}

export interface StudentPerformanceReportFilters {
    search: string;
    clinic_id: number | null;
    period_id: number | null;
}

export interface StudentPerformanceReportPeriod {
    id: number;
    name: string;
}

export interface StudentPerformanceReportClinics {
    id: number;
    name: string;
}