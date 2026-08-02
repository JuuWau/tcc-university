<template>
    <AppLayout>
        <div class="w-full space-y-8 p-8">
            <UserHeader />

            <nav class="flex flex-wrap gap-2 rounded-2xl bg-gray-100 p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="
                        activeTab === tab.key
                            ? 'bg-white text-sky-600 shadow'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                >
                    {{ tab.label }}
                </button>
            </nav>

            <div>
                <UserPersonalData v-if="activeTab === 'personal'" />
                <UserActionLogs v-if="activeTab === 'logs'" />
            </div>
        </div>

        <UserPersonalDataEditModal @updated="onPersonalDataUpdated" />
        <UserRoleEditModal @updated="onRoleUpdated" />
    </AppLayout>
</template>

<script setup lang="ts">
import type { RoleOption, UserTabContext } from '@/keys/users/userKeys';
import { UserTabContextKey } from '@/keys/users/userKeys';
import { UserActionLogsContextKey } from '@/keys/action-logs/userActionLogsKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import UserPersonalDataEditModal from '@/pages/users/components/UserPersonalDataEditModal.vue';
import UserRoleEditModal from '@/pages/users/components/UserRoleEditModal.vue';
import UserHeader from '@/pages/users/UserHeader.vue';
import UserPersonalData from '@/pages/users/tabs/UserPersonalData.vue';
import UserActionLogs from '@/pages/users/tabs/UserActionLogs.vue';
import type { UserForTab } from '@/types/user/user';
import type { UserActionLog } from '@/types/user/userActionLog';
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import { useUserActionLogs } from '@/composables/user/useUserActionLogs';

const page = usePage();
const user = computed(
    () => (page.props as unknown as { user: UserForTab }).user,
);
const roles = computed(
    () => (page.props as unknown as { roles?: RoleOption[] }).roles ?? [],
);

const editPersonalDataModalOpen = ref(false);
const editRoleModalOpen = ref(false);
const actionLogs = useUserActionLogs(
    'users',
    computed(() => user.value.id),
);

provide(UserTabContextKey, {
    user,
    editPersonalDataModalOpen,
    editRoleModalOpen,
    roles,
} as UserTabContext);

provide(UserActionLogsContextKey, actionLogs);

type TabKey = 'personal' | 'logs';

const activeTab = ref<TabKey>('personal');

const tabs: { key: TabKey; label: string }[] = [
    { key: 'personal', label: 'Dados pessoais' },
    { key: 'logs', label: 'Histórico de ações' },
];

function onPersonalDataUpdated() {
    editPersonalDataModalOpen.value = false;
    void router.reload();
}

function onRoleUpdated() {
    editRoleModalOpen.value = false;
    void router.reload();
}

watch(activeTab, async (tab) => {
    if (
        tab === 'logs' &&
        actionLogs.logs.value.data.length === 0
    ) {
        await actionLogs.load();
    }
});
</script>