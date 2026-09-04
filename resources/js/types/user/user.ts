export interface UserInvite {
    id: number;
    user_id: number;
    used_at: string | null;
    expires_at: string;
    token: string;
}

export interface RoleOption {
    id: number;
    name: string;
    slug: string;
}

export interface PersonOption {
    id: number;
    user_id: number;
    name: string;
}

export interface UserWithInvite {
    id: number;
    email: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
    person: PersonOption | null;
    role: RoleOption;
    invite?: UserInvite | null;
}

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

export interface UserForTab {
    id: number;
    email: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
    person: {
        id: number;
        user_id: number;
        name: string;
        cpf?: string | null;
        phone?: string | null;
        birth_date?: string | null;
        address?: Address | null;
    } | null;
    roles: RoleOption[];
    invite?: UserInvite | null;
}
