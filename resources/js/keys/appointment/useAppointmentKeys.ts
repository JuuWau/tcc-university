import type { InjectionKey, Ref } from 'vue';

export interface AppointmentDetailsModal {
    isOpen: Ref<boolean>;
    appointment: Ref<any | null>;
    patientOptions: Ref<any[]>;
    procedureOptions: Ref<any[]>;
}

export interface AppointmentCreateModal {
    isOpen: Ref<boolean>;
    initialData: Ref<{
        date: string;
        start_time: string;
        end_time: string;
    }>;
    patientOptions: Ref<any[]>;
    procedureOptions: Ref<any[]>;
}

export const AppointmentDetailsModalKey: InjectionKey<AppointmentDetailsModal> = Symbol('AppointmentDetailsModalKey');
export const AppointmentCreateModalKey: InjectionKey<AppointmentCreateModal> =
    Symbol('AppointmentCreateModalKey');