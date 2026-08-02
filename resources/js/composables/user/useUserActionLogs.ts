import axios from 'axios';
import { computed, ref, type Ref } from 'vue';
import type { UserActionLog } from '@/types/user/userActionLog';

interface UserActionLogsResponse {
    data: UserActionLog[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

type ActivityEntity =
    | {
          type: 'users';
          id: Ref<number>;
      }
    | {
          type: 'students';
          id: Ref<number>;
      }
    | {
          type: 'patients';
          id: Ref<number>;
      };    

export function useUserActionLogs(
    entity: ActivityEntity,
    entityId: Ref<number>,
) {
    const expandedLogId = ref<number | null>(null);
    const loading = ref(false);
    const modules = ref<string[]>([]);
    const actions = ref<string[]>([]);

    const logs = ref<UserActionLogsResponse>({
        data: [],
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
        },
    });

    const filters = ref({
        page: 1,
        per_page: 15,
        search: '',
        module: null as string | null,
        action: null as string | null,
        date: null as string | null,
        type: 'all' as 'all' | 'performed' | 'received',
    });

    const page = computed(() => logs.value.meta.current_page);
    const perPage = computed(() => logs.value.meta.per_page);
    const total = computed(() => logs.value.meta.total);
    const totalPages = computed(() => logs.value.meta.last_page);

    const fromTo = computed(() => {
        if (!total.value) {
            return '0';
        }

        const from = (page.value - 1) * perPage.value + 1;
        const to = Math.min(page.value * perPage.value, total.value);

        return `${from}-${to} de ${total.value}`;
    });

    async function load() {
        loading.value = true;
        expandedLogId.value = null;

        try {
            const response = await axios.get(
                `/action-logs/${entity}/${entityId.value}/table`,
                {
                    params: filters.value,
                },
            );

            logs.value = response.data;
        } finally {
            loading.value = false;
        }
    }

    async function loadFilters() {
        const { data } = await axios.get('/action-logs/filters');

        modules.value = data.modules;
        actions.value = data.actions;
    }

    async function search() {
        filters.value.page = 1;
        await load();
    }

    async function changePage(page: number) {
        filters.value.page = page;
        await load();
    }

    function clearFilters() {
        filters.value = {
            page: 1,
            per_page: 15,
            search: '',
            module: null,
            action: null,
            date: null,
            type: 'all',
        };

        void load();
    }

    function toggleLog(id: number) {
        expandedLogId.value =
            expandedLogId.value === id ? null : id;
    }

    function isExpanded(id: number) {
        return expandedLogId.value === id;
    }

    return {
        loading,
        logs,
        filters,
        modules,
        actions,
        page,
        perPage,
        total,
        totalPages,
        fromTo,
        expandedLogId,
        toggleLog,
        isExpanded,
        load,
        search,
        changePage,
        clearFilters,
        loadFilters,
    };
}