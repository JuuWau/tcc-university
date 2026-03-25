import type { Clinic } from '@/types/clinic/clinic';
import type { InjectionKey, Ref } from 'vue';

export interface ClinicCreateModal {
    isOpen: Ref<boolean>;
}

export interface ClinicEditModal {
    isOpen: Ref<boolean>;
    clinic: Ref<Clinic | null>;
}

export interface ClinicDeactivateModal {
    isOpen: Ref<boolean>;
    clinic: Ref<Clinic | null>;
}

export interface ClinicActivateModal {
    isOpen: Ref<boolean>;
    clinic: Ref<Clinic | null>;
}

export interface ClinicDeleteModal {
    isOpen: Ref<boolean>;
    clinic: Ref<Clinic | null>;
}

export const ClinicsGroupKey: InjectionKey<Ref<Clinic[]>> = Symbol('ClinicsGroupKey');
export const ClinicCreateKey: InjectionKey<ClinicCreateModal> = Symbol('ClinicCreateKey');
export const ClinicEditKey: InjectionKey<ClinicEditModal> = Symbol('ClinicEditKey');
export const ClinicDeactivateKey: InjectionKey<ClinicDeactivateModal> = Symbol('ClinicDeactivateKey');
export const ClinicActivateKey: InjectionKey<ClinicActivateModal> = Symbol('ClinicActivateKey');
export const ClinicDeleteKey: InjectionKey<ClinicDeleteModal> = Symbol('ClinicDeleteKey');
