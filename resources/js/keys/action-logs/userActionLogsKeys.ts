import type { InjectionKey, Ref } from 'vue';
import type {
    UserActionLogsPagination,
} from '@/types/user/userActionLog';

export interface UserActionLogsContext {
    loading: Ref<boolean>;
    logs: Ref<UserActionLogsPagination>;
    filters: Ref<{
        page: number;
        per_page: number;
        search: string;
        module: string | null;
        action: string | null;
        date: string | null;
        type: 'all' | 'performed' | 'received';
    }>;
    modules: Ref<string[]>;
    actions: Ref<string[]>;
    load: () => Promise<void>;
    loadFilters: () => Promise<void>;
    search: () => Promise<void>;
    changePage: (page: number) => Promise<void>;
    clearFilters: () => void;
}

export const UserActionLogsContextKey =
    Symbol() as InjectionKey<UserActionLogsContext>;