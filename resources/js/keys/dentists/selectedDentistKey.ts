
import type { InjectionKey, Ref } from 'vue'

export interface Speciality {
        id: number
        type: string
}

export interface Address {
        id: number
        cep?: string
        address?: string
        neighborhood?: string
        number?: string
        extra_info?: string
        state?: string
        city?: string
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
        password?: string
        address_id?: number
        address?: Address | null
        specialities?: Speciality[]
}

export const SelectedDentistKey: InjectionKey<Ref<Dentist | null>> = Symbol('selectedDentistKey')