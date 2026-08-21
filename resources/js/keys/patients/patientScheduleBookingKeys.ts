import type { InjectionKey, Ref } from 'vue';
import type { PatientScheduleBookingContext, PatientScheduleCreateModal, PatientScheduleViewModal } from '@/types/patient/patientScheduleBooking';

export const PatientScheduleBookingContextKey: InjectionKey<PatientScheduleBookingContext> = Symbol('PatientScheduleBookingContext');
export const PatientScheduleViewModalKey: InjectionKey<PatientScheduleViewModal> = Symbol('PatientScheduleViewModal');
export const PatientScheduleCreateModalKey: InjectionKey<PatientScheduleCreateModal> = Symbol('PatientScheduleCreateModal');