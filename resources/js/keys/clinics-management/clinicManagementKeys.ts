import type { InjectionKey, Ref } from 'vue';
import type { ClinicManagementRow } from '@/types/clinics-management/clinicManagement';

export interface ClinicManagementContext {
    clinics: Ref<ClinicManagementRow[]>;
    goManageClinic: (clinicId: number) => void;
}

export const ClinicManagementKey = Symbol() as InjectionKey<ClinicManagementContext>;