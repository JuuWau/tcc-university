export interface Address {
    id?: number;
    cep: string | null;
    street: string | null;
    number: string | null;
    neighborhood: string | null;
    city: string | null;
    state: string | null;
    complement: string | null;
}

export interface StudentOption {
    id: number;
    name: string;
}

export const PATIENT_STATUS = {
    ativo: 'Ativo',
    inativo: 'Inativo',
    tratamento: 'Tratamento',
    pausa_tratamento: 'Pausa no Tratamento',
    abandono: 'Abandono',
    concluido: 'Concluído',
    transferencia: 'Transferência',
} as const;

export type PatientStatusKey = keyof typeof PATIENT_STATUS;

export interface PatientWithInvite {
    id: number;
    university_id: number;
    student_id: number;
    name: string;
    cpf: string | null;
    birth_date: string | null;
    phone: string | null;
    email: string | null;
    status?: PatientStatusKey;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
    student?: StudentOption | null;
    address?: Address | null;
}

export interface PatientForTab {
    id: number;
    university_id: number;
    student_ids: number[];
    code: string;
    name: string;
    cpf: string | null;
    birth_date: string | null;
    patient_type: 'adulto' | 'pediatria';
    phone: string | null;   
    email: string | null;
    status?: PatientStatusKey;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
    student?: StudentOption | null;
    address?: Address | null;
}
