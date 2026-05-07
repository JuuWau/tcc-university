import type { OpenClinicScheduleRow } from '@/types/schedule/openClinicSchedules';
import type { InjectionKey, Ref } from 'vue';

export interface ScheduleSlotEditModal {
    isOpen: Ref<boolean>;
    row: Ref<OpenClinicScheduleRow | null>;
}

export interface ScheduleSlotDeleteModal {
    isOpen: Ref<boolean>;
    row: Ref<OpenClinicScheduleRow | null>;
}

export interface ScheduleSlotCreateModal {
    isOpen: Ref<boolean>;
    clinicId?: Ref<number | null>;
    periodId?: Ref<number | null>;
}

export interface ScheduleSlotDeleteMultipleModal {
    isOpen: Ref<boolean>;
    slots: Ref<OpenClinicScheduleRow[]>;
}

export interface ScheduleSlotEditMultipleModal {
    isOpen: Ref<boolean>;
    slots: Ref<OpenClinicScheduleRow[]>;
}

export const ScheduleSlotEditKey: InjectionKey<ScheduleSlotEditModal> =
    Symbol('ScheduleSlotEditKey');

export const ScheduleSlotDeleteKey: InjectionKey<ScheduleSlotDeleteModal> =
    Symbol('ScheduleSlotDeleteKey');

export const ScheduleSlotCreateKey: InjectionKey<ScheduleSlotCreateModal> =
    Symbol('ScheduleSlotCreateKey');

export const ScheduleSlotDeleteMultipleKey: InjectionKey<ScheduleSlotDeleteMultipleModal> =
    Symbol('ScheduleSlotDeleteMultipleKey');

export const ScheduleSlotEditMultipleKey: InjectionKey<ScheduleSlotEditMultipleModal> =
    Symbol('ScheduleSlotEditMultipleModal');