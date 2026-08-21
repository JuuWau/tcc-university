import type { ComputedRef, Ref } from 'vue';
export interface PatientScheduleBookingOption {
    value: number;
    label: string;
}

export interface PatientScheduleBookingDay {
    date: string;
    label: string;
}

export interface PatientScheduleBookingContext {
    patientId: number;
    patientName: string;

    clinics: Ref<PatientScheduleBookingOption[]>;
    periods: Ref<PatientScheduleBookingOption[]>;
    students: Ref<PatientScheduleBookingOption[]>;

    durations: PatientScheduleBookingOption[];

    clinicId: Ref<number | null>;
    periodId: Ref<number | null>;
    studentId: Ref<number | null>;
    duration: Ref<number | null>;

    selectedDate: Ref<string | null>;
    selectedTime: Ref<string | null>;

    procedureOptions: Ref<PatientScheduleBookingOption[]>;

    availableDays: Ref<PatientScheduleBookingDay[]>;
    availableTimes: Ref<PatientScheduleBookingTime[]>;
    appointments: Ref<PatientScheduleBookingAppointment[]>;

    loadingClinics: Ref<boolean>;
    loadingPeriods: Ref<boolean>;
    loadingStudents: Ref<boolean>;
    loadingAvailability: Ref<boolean>;

    canLoadAvailability: ComputedRef<boolean>;
    canConfirm: ComputedRef<boolean>;
    hasSelectedFilters: ComputedRef<boolean>;
    canShowCalendar: ComputedRef<boolean>;

    loadClinics: () => Promise<void>;
    loadPeriods: () => Promise<void>;
    loadStudents: () => Promise<void>;
    loadProcedures: () => Promise<void>;
    loadAvailableDays: () => Promise<void>;
    loadAvailableTimes: () => Promise<void>;
    updateAppointmentTime: (
        appointmentId: number,
        scheduledStartAt: string,
        scheduledEndAt: string,
    ) => Promise<void>;

    previousMonth: () => Promise<void>;
    nextMonth: () => Promise<void>;

    selectDate: (date: string) => Promise<void>;

    resetAvailability: () => void;
    resetBooking: () => void;
}

export interface PatientScheduleBookingTime {
    schedule_enrollment_id: number;
    start: string;
    end: string;
    allow_procedure_booking: boolean;
}

export interface PatientScheduleBookingAppointment {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    patient: string | null;
    patient_id: number;
    status: string;
    notes: string | null;
    procedure_id: number | null;
    procedure: string | null;
    allow_procedure_booking: boolean;
    schedule_enrollment_id: number;
}

export interface PatientScheduleViewModal {
    isOpen: Ref<boolean>;
    appointment: Ref<PatientScheduleBookingAppointment | null>;
    initialData: Ref<{
        date: string;
        start_time: string;
        end_time: string;
    }>;
    patientOptions: Ref<any[]>;
    procedureOptions: Ref<any[]>;
}

export interface PatientScheduleCreateData {
    schedule_enrollment_id: number | null;
    date: string;
    start_time: string;
    end_time: string;
    allow_procedure_booking: boolean;
    patient_id: number;
    patient: string;
    status: string;
    notes: string;
}

export interface PatientScheduleCreateModal {
    isOpen: Ref<boolean>;
    initialData: Ref<PatientScheduleCreateData>;
    procedureOptions: Ref<PatientScheduleBookingOption[]>;
}