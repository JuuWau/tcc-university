import type { InjectionKey, Ref } from 'vue';
import type { PatientClinicStatus } from '@/types/patient/patientClinics';
import { PatientForTab } from '@/types/patient/patient';

export interface PatientClinicsContext {
    loading: Ref<boolean>;
    activeStatus: Ref<PatientClinicStatus>;
    setStatus: (
        status: PatientClinicStatus,
    ) => void;
    goToPage: (
        newPage: number,
    ) => void;
    page: Ref<number>;
    perPage: Ref<number>;
    total: Ref<number>;
    totalPages: Ref<number>;

    clinics: Ref<any[]>;
}

export const PatientClinicEnrollKey = Symbol() as InjectionKey<{
    isOpen: Ref<boolean>;
    patient: Ref<PatientForTab  | null>;
    clinicId: Ref<number | null>;
}>;

export const PatientClinicRemoveEnrollmentKey = Symbol() as InjectionKey<{
    isOpen: Ref<boolean>;
    patient: Ref<PatientForTab  | null>;
    clinicId: Ref<number | null>;
}>;

export const PatientClinicCreateWaitingListKey  = Symbol() as InjectionKey<{
    isOpen: Ref<boolean>;
    patient: Ref<PatientForTab  | null>;
    clinicId: Ref<number | null>;
}>;

export type RefreshTableFn = () => void;

export const PatientClinicsKey = Symbol() as InjectionKey<PatientClinicsContext>;
export const RefreshTableKey: InjectionKey<Ref<RefreshTableFn | null>> = Symbol('RefreshTableKey');