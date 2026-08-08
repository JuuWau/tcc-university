export interface AppointmentsFilters {
    clinic: number | null;
    responsible: number | null;
    student: number | null;
    period: number | null;
    status: string | null;
    start_date: string | null;
    end_date: string | null;
}