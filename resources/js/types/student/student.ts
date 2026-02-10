import type { Period } from '../period';

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

export interface Student {
    id: number;
    registration: string;

    person: {
        id: number;
        name: string;
        cpf: string | null;
        phone: string | null;
        birth_date?: string | null;
        address?: Address | null;
    };

    user: {
        id: number;
        email: string;
    };

    periods: Period[];

    created_at?: string;
    updated_at?: string;
}
