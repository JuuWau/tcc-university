// keys/specialitiesKey.ts
import type { InjectionKey } from 'vue'

export interface Speciality {
        id: number
        type: string
}

export const SpecialitiesKey: InjectionKey<Speciality[]> = Symbol('specialities')