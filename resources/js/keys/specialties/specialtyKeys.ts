import type { Ref } from "vue";
import { Specialty } from "@/types/specialty";
import type { InjectionKey } from "vue";

export interface SpecialtyCreateModal {
    isOpen: Ref<boolean>;
}

export interface SpecialtyEditModal {
    specialty: Ref<Specialty | null>;
}

export interface SpecialtyDeleteModal {
    specialty: Ref<Specialty | null>;
}

export interface SelectedSpecialtyKey {
    specialty: Ref<Specialty | null>;
}

export interface SpecialtiesResponse {
    specialties: Ref<Specialty[] | []>;
}

export interface SpecialtiesGroup {
    specialties: Ref<Specialty[] | []>;
}

export const SpecialtyCreateKey: InjectionKey<SpecialtyCreateModal> = Symbol("SpecialtyCreateKey");
export const SpecialtyEditKey: InjectionKey<SpecialtyEditModal> = Symbol("SpecialtyEditKey");
export const SpecialtyDeleteKey: InjectionKey<SpecialtyDeleteModal> = Symbol("SpecialtyDeleteKey");
export const SelectedSpecialtyKey: InjectionKey<Ref<SelectedSpecialtyKey>> = Symbol('SelectedSpecialtyKey');
export const SpecialtiesKey: InjectionKey<Ref<SpecialtiesResponse>> = Symbol('SpecialtiesKey');
export const SpecialtiesGroupKey: InjectionKey<Ref<SpecialtiesGroup>> = Symbol('SpecialtiesGroupKey');