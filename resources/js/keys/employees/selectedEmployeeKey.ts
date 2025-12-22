import type { InjectionKey, Ref } from "vue"

export interface Employee {
  id: number
  name: string
  email: string
  photo?: string | null
  permission_id: number
  permission_type: string
  birth_date?: string
  cpf?: string
  address_id?: number
  address?: Address | null
}

export interface Address {
  cep?: string
  address?: string
  neighborhood?: string
  number?: string
  extra_info?: string
  state?: string
  city?: string
}

export const SelectedEmployeeKey: InjectionKey<Ref<Employee | null>> = Symbol("SelectedEmployee")