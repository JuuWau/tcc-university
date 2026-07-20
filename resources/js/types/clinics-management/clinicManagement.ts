export interface ClinicManagementRow {
    clinic_id: number;
    clinic_name: string;
    active_patients_count: number;
    waiting_patients_count: number;
}

export type ClinicPatientStatus = (typeof CLINIC_PATIENT_STATUS)[keyof typeof CLINIC_PATIENT_STATUS];

export const CLINIC_PATIENT_STATUS = {
    ALL: 'all',
    ENROLLED: 'enrolled',
    WAITING: 'waiting',
} as const;

export interface ClinicPatientRow {
    id: number;
    patient_id: number;
    name: string;
    cpf: string;
    phone: string | null;
    status: ClinicPatientStatus;
    joined_at: string | null;
    enrolled_at: string | null;
}