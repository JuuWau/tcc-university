import type { InjectionKey, Ref } from 'vue';

export const LoadingKey: InjectionKey<Ref<boolean>> = Symbol('LoadingKey');
