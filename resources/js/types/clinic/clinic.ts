export interface Clinic {
    id: number;
    university_id: number;
    name: string;
    specialty_id: number;
    active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}
