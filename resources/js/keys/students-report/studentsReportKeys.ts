import type { InjectionKey } from 'vue';
import type { useStudentsReport } from '@/composables/students-report/useStudentsReport';

export type StudentsReportContext = ReturnType<typeof useStudentsReport>;

export const StudentsReportKey: InjectionKey<StudentsReportContext> = Symbol('StudentsReport');