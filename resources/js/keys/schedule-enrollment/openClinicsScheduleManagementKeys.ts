import type { InjectionKey, Ref } from 'vue';

export type OpenClinicScheduleRow = {
    clinic_id: number;
    clinic_name: string;
    open_days_count: number;
    open_slots_count: number;
    next_open_day: string | null;
    next_start_time: string | null;
    next_end_time: string | null;
};

export interface OpenClinicsManagementScheduleContext {
    clinics: Ref<OpenClinicScheduleRow[]>;
    goManageClinicSchedule: (clinicId: number) => void;
}

export const OpenClinicsManagementScheduleKey: InjectionKey<OpenClinicsManagementScheduleContext> =
    Symbol('OpenClinicsManagementScheduleKey');
