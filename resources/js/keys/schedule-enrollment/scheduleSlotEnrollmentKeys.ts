import type { OpenClinicScheduleEnrollmentRow } from '@/types/schedule-enrollment/openClinicSchedulesEnrollment.ts';
import type { InjectionKey, Ref } from 'vue';

export interface ScheduleSlotEnrollmentModal {
    isOpen: Ref<boolean>;
    slot: Ref<OpenClinicScheduleEnrollmentRow | null>;
}
export interface ScheduleSlotEnrollmentMultipleModal {
    isOpen: Ref<boolean>;
    slots: Ref<OpenClinicScheduleEnrollmentRow[]>;
}

export const ScheduleSlotEnrollmentKey: InjectionKey<ScheduleSlotEnrollmentModal> =
    Symbol('ScheduleSlotEnrollmentKey');

export const ScheduleSlotEnrollmentMultipleKey: InjectionKey<ScheduleSlotEnrollmentMultipleModal> =
    Symbol('ScheduleSlotEnrollmentMultipleModal');