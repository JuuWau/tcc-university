import type { ComputedRef, Ref } from 'vue';
import type { UserForTab, UserWithInvite } from '@/types/user/user';
import type { InjectionKey } from 'vue';

export interface RoleOption {
    id: number;
    name: string;
    slug: string;
}

export interface UserCreateModal {
    isOpen: Ref<boolean>;
}

export interface UserDeactivateModal {
    isOpen: Ref<boolean>;
    user: Ref<UserWithInvite | null>;
}

export interface UserViewModal {
    isOpen: Ref<boolean>;
    user: Ref<UserWithInvite | null>;
}

export interface UserDeleteModal {
    isOpen: Ref<boolean>;
    user: Ref<UserWithInvite | null>;
}

export interface UserActivateModal {
    isOpen: Ref<boolean>;
    user: Ref<UserWithInvite | null>;
}

export type RefreshTableFn = () => void;

export interface UserTabContext {
    user: ComputedRef<UserForTab>;
    editPersonalDataModalOpen: Ref<boolean>;
    editRoleModalOpen: Ref<boolean>;
    roles: ComputedRef<RoleOption[]> | Ref<RoleOption[]>;
}

export const UserTabContextKey: InjectionKey<UserTabContext> = Symbol('UserTabContextKey');

export const UserCreateKey: InjectionKey<UserCreateModal> = Symbol('UserCreateKey');
export const RefreshTableKey: InjectionKey<Ref<RefreshTableFn | null>> = Symbol('RefreshTableKey');
export const UserDeactivateKey: InjectionKey<UserDeactivateModal> = Symbol('UserDeactivateKey');
export const UserViewKey: InjectionKey<UserViewModal> = Symbol('UserViewKey');
export const UserDeleteKey: InjectionKey<UserDeleteModal> = Symbol('UserDeleteKey');
export const UserActivateKey: InjectionKey<UserActivateModal> = Symbol('UserActivateKey');
