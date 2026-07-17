import type { InjectionKey } from 'vue';
import type { AppointmentsConfirmationContext } from '@/types/appointments-confirmation/appointmentsConfirmation';

export const AppointmentsConfirmationKey = Symbol() as InjectionKey<AppointmentsConfirmationContext>;