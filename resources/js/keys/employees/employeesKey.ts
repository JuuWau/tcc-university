import type { InjectionKey } from "vue"

export interface Employee {
        id: number
        name: string
        email: string
        photo?: string | null
        permission_id: number
}

export interface EmployeesResponse {
        data: Employee[]
        prev_page_url?: string | null
        next_page_url?: string | null
}

export const EmployeesKey: InjectionKey<EmployeesResponse> = Symbol("employees")
