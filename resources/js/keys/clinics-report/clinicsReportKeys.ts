import type { InjectionKey } from 'vue';

import { useClinicsReport } from '@/composables/clinics-report/useClinicsReport';

export type ClinicsReportContext = ReturnType<typeof useClinicsReport>;

export const ClinicsReportKey: InjectionKey<ClinicsReportContext> = Symbol('ClinicsReport');