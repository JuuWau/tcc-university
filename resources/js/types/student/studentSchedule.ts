export interface MonthDay {
    day: number;
    date: string;
}

export interface MonthData {
    month: number;
    label: string;
    days: MonthDay[];
}

export interface ClinicOption {
    label: string;
    value: number;
}

export interface ScheduleEvent {
    id: number;
    date: string;
    time: string;
    procedure: string;
    patient: string;
}