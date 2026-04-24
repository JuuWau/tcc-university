import type { InjectionKey, Ref } from 'vue';

export interface OpenScheduleState {
    loading: Ref<boolean>;
}

export const OpenScheduleKey: InjectionKey<OpenScheduleState> =
    Symbol('OpenScheduleKey');
