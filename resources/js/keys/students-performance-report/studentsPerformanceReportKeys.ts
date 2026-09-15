import type { InjectionKey } from 'vue';
import type { useStudentsPerformanceReport } from '@/composables/students-performance-report/useStudentsPerformanceReport';

export type StudentsPerformanceReportContext = ReturnType<typeof useStudentsPerformanceReport>;

export const StudentsPerformanceReportKey: InjectionKey<StudentsPerformanceReportContext> = Symbol('StudentsPerformanceReport');