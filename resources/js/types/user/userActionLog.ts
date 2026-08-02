export interface ActivityLogChange {
    old: unknown;
    new: unknown;
}

export interface UserActionLog {
    id: number;
    module: string;
    action: 'create' | 'update' | 'delete';
    description: string;
    changes: Record<string, ActivityLogChange> | null;
    created_at: string;
}

export interface UserActionLogsPagination {
    data: UserActionLog[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    last_page: number;
}