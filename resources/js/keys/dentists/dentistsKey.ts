import type { InjectionKey, Ref } from "vue"

export interface Speciality {
    id: number
    type: string
}

export interface Dentist {
    id: number
    name: string
    email: string
    photo?: string | null
    birth_date?: string
    cpf?: string
    cro_number: string
    cro_uf: string
    specialities?: Speciality[]
}

export interface DentistsResponse {
    data: Dentist[]
    prev_page_url?: string | null
    next_page_url?: string | null
}

export const DentistsKey: InjectionKey<Ref<DentistsResponse>> = Symbol("dentistsKey")
