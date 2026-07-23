export type PatientClinicStatus =
    (typeof PATIENT_CLINIC_STATUS)[keyof typeof PATIENT_CLINIC_STATUS];

export const PATIENT_CLINIC_STATUS = {
    WAITING: 'waiting', 
    ENROLLED: 'enrolled',
} as const;

export interface PatientClinicRow {
    clinic_id: number;
    clinic_name: string;
    status: PatientClinicStatus;
    joined_at: string | null;
    enrolled_at: string | null;
    period_name: string | null;
}