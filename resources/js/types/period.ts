export interface Period {
    id: number;
    academic_year: number;
    semester: number;
    calendar_year: number;
    specialties?: { id: number; name: string }[];
    created_at?: string;
    updated_at?: string;
}