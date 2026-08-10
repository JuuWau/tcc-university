export interface StudentReport {
    id: number;
    name: string;
    registration: string;
    period: string | null;
    status: string;
    invitation_status: string;
    created_at: string;
}

export interface StudentReportSummary {
    total: number;
    active: number;
    inactive: number;
    invitation_accepted: number;
    invitation_pending: number;
}

export interface StudentReportFilters {
    search: string;
    period_id: number | null;
    status: string | null;
    invitation_status: string | null;
}

export interface StudentReportPeriod {
    id: number;
    name: string;
}