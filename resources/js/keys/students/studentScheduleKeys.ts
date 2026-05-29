import type { InjectionKey } from 'vue';
import type { useStudentSchedule } from '@/composables/students/useStudentSchedule';

export type StudentScheduleContext = ReturnType<typeof useStudentSchedule>;

export const StudentScheduleContextKey = Symbol() as InjectionKey<StudentScheduleContext>;