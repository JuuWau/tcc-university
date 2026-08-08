import type { InjectionKey } from 'vue';
import type { Appointment } from '@/types/appointments-report/appointment';

export const AppointmentsKey = Symbol() as InjectionKey<Appointment>;