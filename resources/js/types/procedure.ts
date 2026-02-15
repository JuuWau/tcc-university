export interface Procedure {
    id: number;
    name: string;
    specialty_id: number;
    specialty?: { id: number; name: string };
    created_at?: string;
    updated_at?: string;
}
