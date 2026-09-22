export interface PrePatientClinic {
    id: number;
    name: string;
    enrolled_at: string | null;
}

export interface PrePatient {
    id: number;
    name: string;
    cpf: string | null;
    birth_date: string | null;
    biological_sex: string;
    patient_type:string;
    phone: string | null;
    email: string | null;
    status: string;
    clinics: PrePatientClinic[];
    created_at: string | null;
}

export interface PrePatientForTab {
	id: number;
	pre_patient_id: number;
	name: string;
	cpf: string | null;
	phone: string | null;
	status: 'waiting' | 'enrolled';
	enrolled_at: string;
};