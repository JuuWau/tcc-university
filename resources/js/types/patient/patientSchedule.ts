export interface PatientSchedule {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    status: string;
    clinic: {
        id: number;
        name: string;
    } | null;
    period: {
        id: number;
        name: string;
    } | null;
    student: {
        id: number;
        name: string;
    } | null;
    procedure: {
        id: number;
        name: string;
    } | null;
    responsibles: {
        id: number;
        name: string;
    }[];
    length: number;
}