import { ComputedRef, Ref } from "vue";

export interface PatientReport {
    id: number;
    name: string;
    code: string;
    cpf: string | null;
    phone: string | null;
    birth_date: string | null;
    patient_type: 'pediatria' | 'adulto';
    status:
        | 'ativo'
        | 'inativo'
        | 'tratamento'
        | 'pausa_tratamento'
        | 'abandono'
        | 'concluido'
        | 'transferencia';
    created_at: string | null;
}

export interface PatientReportFilters {
    search: string | null;
    patient_type: string | null;
    status: string | null;
}

export interface PatientReportSummary {
    total: number;
    ativo: number;
    inativo: number;
    tratamento: number;
    pausa_tratamento: number;
    abandono: number;
    concluido: number;
    transferencia: number;
}

export interface PatientsReportContext {
    filters: Ref<PatientReportFilters>;
    patients: Ref<PatientReport[]>;
    summary: Ref<PatientReportSummary>;

    loading: Ref<boolean>;

    page: Ref<number>;
    perPage: Ref<number>;
    total: Ref<number>;
    totalPages: Ref<number>;

    hasActiveFilters: ComputedRef<boolean>;
    activeFiltersCount: ComputedRef<number>;

    load: () => Promise<void>;
    search: () => void;
    goToPage: (page: number) => void;
    clearFilters: () => void;
    exportExcel: () => Promise<void>;
}