export interface Appointment {
    id: number;
    patient: {
        id: number;
        name: string;
    };
    student: {
        id: number;

        user: {
            id: number;
            name: string;
        };
    };
    responsible: {
        id: number;
        name: string;
    };
    procedure: {
        id: number;
        name: string;
    };
    slot: {
        id: number;

        clinic: {
            id: number;
            name: string;
        };
    };
    scheduled_start_at: string;
    scheduled_end_at: string;
    status: string;
    notes: string | null;
}