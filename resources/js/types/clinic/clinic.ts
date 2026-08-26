export interface Specialty {
    id: number;
    university_id: number;
    name: string;
    active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}

export interface Clinic {
    id: number;
    university_id: number;
    name: string;
    specialties: Specialty[];
    active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}