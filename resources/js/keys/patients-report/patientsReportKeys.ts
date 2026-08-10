import type { InjectionKey } from 'vue';
import type { PatientsReportContext, } from '@/types/patients-report/patientsReport';

export const PatientsReportKey: InjectionKey<PatientsReportContext> = Symbol('PatientsReport');