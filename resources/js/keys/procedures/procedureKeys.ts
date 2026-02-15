import type { Ref } from 'vue';
import type { Procedure } from '@/types/procedure';
import type { InjectionKey } from 'vue';

export interface ProcedureCreateModal {
    isOpen: Ref<boolean>;
}

export interface ProcedureEditModal {
    isOpen: Ref<boolean>;
    procedure: Ref<Procedure | null>;
}

export interface ProcedureDeleteModal {
    isOpen: Ref<boolean>;
    procedure: Ref<Procedure | null>;
}

export type ProceduresGroup = Procedure[];

export type ProcedureSpecialtyOption = { id: number; name: string };

export const ProcedureCreateKey: InjectionKey<ProcedureCreateModal> =
    Symbol('ProcedureCreateKey');
export const ProcedureEditKey: InjectionKey<ProcedureEditModal> =
    Symbol('ProcedureEditKey');
export const ProcedureDeleteKey: InjectionKey<ProcedureDeleteModal> =
    Symbol('ProcedureDeleteKey');
export const ProceduresGroupKey: InjectionKey<Ref<ProceduresGroup>> =
    Symbol('ProceduresGroupKey');
export const ProceduresSpecialtiesKey: InjectionKey<ProcedureSpecialtyOption[]> =
    Symbol('ProceduresSpecialtiesKey');
