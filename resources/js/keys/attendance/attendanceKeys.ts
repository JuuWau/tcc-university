import type { InjectionKey, Ref } from 'vue';

export interface AttendanceContext {
    clinic: Ref<any>;
    periods: Ref<any[]>;
    selectedPeriodId: Ref<number | null>;
    selectedDate: Ref<number | null>;
    dates: Ref<any[]>;
    students: Ref<any[]>;
}

export const AttendanceKey = Symbol('AttendanceKey') as InjectionKey<AttendanceContext>;