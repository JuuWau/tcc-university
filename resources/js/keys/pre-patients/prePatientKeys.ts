import type { PrePatient } from '@/types/pre-patients/prePatient';
import type { InjectionKey, Ref } from 'vue';

export const PrePatientsGroupKey: InjectionKey<Ref<PrePatient[]>> =
    Symbol('PrePatientsGroup');

export const RefreshTableKey: InjectionKey<Ref<(() => void) | null>> =
    Symbol('RefreshTable');

export const PrePatientCreateKey: InjectionKey<{ isOpen: Ref<boolean> }> =
    Symbol('PrePatientCreate');

export const PrePatientEditKey: InjectionKey<{
    isOpen: Ref<boolean>;
    prePatient: Ref<PrePatient | null>;
}> = Symbol('PrePatientEdit');

export const PrePatientDeleteKey: InjectionKey<{
    isOpen: Ref<boolean>;
    prePatient: Ref<PrePatient | null>;
}> = Symbol('PrePatientDelete');

export const PrePatientConvertKey: InjectionKey<{
    isOpen: Ref<boolean>;
    prePatient: Ref<PrePatient | null>;
}> = Symbol('PrePatientConvert');
