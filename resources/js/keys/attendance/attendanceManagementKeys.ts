import type { InjectionKey, Ref } from 'vue';
import type { AttendanceClinic } from '@/types/attendance/attendance';

export interface AttendanceManagementContext {
    clinics: Ref<AttendanceClinic[]>;
    goManageClinic: (clinicId: number) => void;
}

export const AttendanceManagementKey = Symbol('AttendanceManagement') as InjectionKey<AttendanceManagementContext>;