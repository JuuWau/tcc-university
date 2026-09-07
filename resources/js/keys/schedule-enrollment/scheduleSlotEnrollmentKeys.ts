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

export type RefreshTableFn = () => void;

export const ScheduleSlotEnrollmentKey: InjectionKey<ScheduleSlotEnrollmentModal> =
    Symbol('ScheduleSlotEnrollmentKey');

export const ScheduleSlotEnrollmentMultipleKey: InjectionKey<ScheduleSlotEnrollmentMultipleModal> =
    Symbol('ScheduleSlotEnrollmentMultipleModal');

export const RefreshTableKey: InjectionKey<Ref<RefreshTableFn | null>> =
    Symbol('ScheduleEnrollmentRefreshTableKey');