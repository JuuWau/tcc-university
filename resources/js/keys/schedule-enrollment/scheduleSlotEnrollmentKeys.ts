import type { OpenClinicScheduleEnrollmentRow } from '@/types/schedule-enrollment/openClinicSchedulesEnrollment.ts';
import type { InjectionKey, Ref } from 'vue';

export interface ScheduleSlotEditModal {
    isOpen: Ref<boolean>;
    row: Ref<OpenClinicScheduleEnrollmentRow | null>;
}

export interface ScheduleSlotDeleteModal {
    isOpen: Ref<boolean>;
    row: Ref<OpenClinicScheduleEnrollmentRow | null>;
}

export interface ScheduleSlotCreateModal {
    isOpen: Ref<boolean>;
    clinicId?: Ref<number | null>;
    periodId?: Ref<number | null>;
}

export interface ScheduleSlotDeleteMultipleModal {
    isOpen: Ref<boolean>;
    slots: Ref<OpenClinicScheduleEnrollmentRow[]>;
}

export interface ScheduleSlotEnrollmenrMultipleModal {
    isOpen: Ref<boolean>;
    slots: Ref<OpenClinicScheduleEnrollmentRow[]>;
}

export const ScheduleSlotEditKey: InjectionKey<ScheduleSlotEditModal> =
    Symbol('ScheduleSlotEditKey');

export const ScheduleSlotDeleteKey: InjectionKey<ScheduleSlotDeleteModal> =
    Symbol('ScheduleSlotDeleteKey');

export const ScheduleSlotCreateKey: InjectionKey<ScheduleSlotCreateModal> =
    Symbol('ScheduleSlotCreateKey');

export const ScheduleSlotDeleteMultipleKey: InjectionKey<ScheduleSlotDeleteMultipleModal> =
    Symbol('ScheduleSlotDeleteMultipleKey');

export const ScheduleSlotEnrollmentMultipleKey: InjectionKey<ScheduleSlotEnrollmenrMultipleModal> =
    Symbol('ScheduleSlotEnrollmenrMultipleModal');