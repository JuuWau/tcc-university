import type { ComputedRef, Ref } from 'vue';
import type {
    PatientForTab,
    PatientWithInvite,
    StudentOption,
} from '@/types/patient/patient';
import type { InjectionKey } from 'vue';

export interface PatientCreateModal {
    isOpen: Ref<boolean>;
}

export interface PatientDeactivateModal {
    isOpen: Ref<boolean>;
    patient: Ref<PatientWithInvite | null>;
}

export interface PatientViewModal {
    isOpen: Ref<boolean>;
    patient: Ref<PatientWithInvite | null>;
}

export interface PatientDeleteModal {
    isOpen: Ref<boolean>;
    patient: Ref<PatientWithInvite | null>;
}

export interface PatientActivateModal {
    isOpen: Ref<boolean>;
    patient: Ref<PatientWithInvite | null>;
}

export type RefreshTableFn = () => void;

export interface PatientTabContext {
    patient: ComputedRef<PatientForTab>;
    editPersonalDataModalOpen: Ref<boolean>;
    editStudentModalOpen: Ref<boolean>;
    students: ComputedRef<StudentOption[]> | Ref<StudentOption[]>;
}

export interface PatientsImportModal {
    isOpen: Ref<boolean>;
}

export const PatientTabContextKey: InjectionKey<PatientTabContext> = Symbol('PatientTabContextKey');

export const PatientCreateKey: InjectionKey<PatientCreateModal> = Symbol('PatientCreateKey');
export const RefreshTableKey: InjectionKey<Ref<RefreshTableFn | null>> = Symbol('RefreshTableKey');
export const PatientDeactivateKey: InjectionKey<PatientDeactivateModal> = Symbol('PatientDeactivateKey');
export const PatientViewKey: InjectionKey<PatientViewModal> = Symbol('PatientViewKey');
export const PatientDeleteKey: InjectionKey<PatientDeleteModal> = Symbol('PatientDeleteKey');
export const PatientActivateKey: InjectionKey<PatientActivateModal> = Symbol('PatientActivateKey');
export const PatientsImportKey: InjectionKey<PatientsImportModal> = Symbol('PatientsImportKey');
