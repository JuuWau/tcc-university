import type { InjectionKey } from "vue"

export interface Permission {
        id: number
        type: string
}

export const PermissionsKey: InjectionKey<Permission[]> = Symbol("permissions")
