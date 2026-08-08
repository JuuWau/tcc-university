export interface AppointmentsSummary {
    total: number;
    scheduled: number;
    confirmed: number;
    completed: number;
    canceled: number;
    no_show: number;
    rescheduled: number;
}