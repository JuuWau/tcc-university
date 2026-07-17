import type { ComputedRef, Ref } from 'vue';

export interface AppointmentConfirmation {
    id: number;
    scheduled_start_at: string;
    scheduled_end_at: string;
    student: {
        id: number;
        name: string;
    };
    clinic: {
        id: number;
        name: string;
    };
    status: AppointmentConfirmationStatus;
}

export interface AppointmentsConfirmationContext {
    appointments: ComputedRef<AppointmentConfirmation[]>;
    clinics: ComputedRef<ClinicOption[]>;
    periods: ComputedRef<PeriodOption[]>;
    filters: Ref<AppointmentsConfirmationFilters>;
    loading: Ref<boolean>;
    searchAppointments(): void;
    clearFilters(): void;
    updateAppointmentStatus(id: number, status: string): Promise<void>;
}

export type AppointmentConfirmationStatus =
    | 'scheduled'
    | 'confirmed'
    | 'no_show'
    | 'canceled';

export interface AppointmentsConfirmationFilters {
    date: string | null;
    clinic_id: number | null;
    period_id: string | null;
    status: AppointmentConfirmationStatus | null;
}

export interface ClinicOption {
    id: number;
    label: string;
}

export interface PeriodOption {
    id: string;
    label: string;
}