import type { InjectionKey, Ref } from 'vue';
import type { ClinicPatientStatus, } from '@/types/clinics-management/clinicManagement';
import { PatientForTab } from '@/types/patient/patient';

export interface ClinicManagementShowContext {
    loading: Ref<boolean>;
    activeStatus: Ref<ClinicPatientStatus>;
    setStatus: (
        status: ClinicPatientStatus,
    ) => void;
    goToPage: (
        newPage: number
    ) => void;
}

export const ClinicEnrollKey = Symbol() as InjectionKey<{
    isOpen: Ref<boolean>;
    patient: Ref<PatientForTab  | null>;
}>;

export const ClinicRemoveEnrollmentKey = Symbol() as InjectionKey<{
    isOpen: Ref<boolean>;
    patient: Ref<PatientForTab  | null>;
}>;

export const ClinicCreateWaitingListKey  = Symbol() as InjectionKey<{
    isOpen: Ref<boolean>;
}>;

export type RefreshTableFn = () => void;

export const ClinicManagementShowKey = Symbol() as InjectionKey<ClinicManagementShowContext>;
export const RefreshTableKey: InjectionKey<Ref<RefreshTableFn | null>> = Symbol('RefreshTableKey');