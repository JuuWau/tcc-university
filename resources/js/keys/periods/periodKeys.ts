import type { Ref } from "vue";
import { Period } from "@/types/period";
import type { InjectionKey } from "vue";

export interface PeriodCreateModal {
    isOpen: Ref<boolean>;
}

export interface PeriodEditModal {
    period: Ref<Period | null>;
}

export interface PeriodDeleteModal {
    period: Ref<Period | null>;
}

export interface SelectedPeriodKey {
    period: Ref<Period | null>;
}

export interface PeriodsResponse {
    periods: Ref<Period[] | []>;
}

export interface PeriodsGroup {
    periods: Ref<Period[] | []>;
}

export const PeriodCreateKey: InjectionKey<PeriodCreateModal> = Symbol("PeriodCreateKey");
export const PeriodEditKey: InjectionKey<PeriodEditModal> = Symbol("PeriodEditKey");
export const PeriodDeleteKey: InjectionKey<PeriodDeleteModal> = Symbol("PeriodDeleteKey");
export const SelectedPeriodKey: InjectionKey<Ref<SelectedPeriodKey>> = Symbol('SelectedPeriodKey');
export const PeriodsKey: InjectionKey<Ref<PeriodsResponse>> = Symbol('PeriodsKey');
export const PeriodsGroupKey: InjectionKey<Ref<PeriodsGroup>> = Symbol('PeriodsGroupKey');