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
            </div>
        </div>

        <UserPersonalDataEditModal @updated="onPersonalDataUpdated" />
        <UserRoleEditModal @updated="onRoleUpdated" />
    </AppLayout>
</template>

<script setup lang="ts">
import type { RoleOption, UserTabContext } from '@/keys/users/userKeys';
import { UserTabContextKey } from '@/keys/users/userKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import UserPersonalDataEditModal from '@/pages/users/components/UserPersonalDataEditModal.vue';
import UserRoleEditModal from '@/pages/users/components/UserRoleEditModal.vue';
import UserHeader from '@/pages/users/UserHeader.vue';
import UserPersonalData from '@/pages/users/tabs/UserPersonalData.vue';
import type { UserForTab } from '@/types/user/user';
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref } from 'vue';

const page = usePage();
const user = computed(
    () => (page.props as unknown as { user: UserForTab }).user,
);
const roles = computed(
    () => (page.props as unknown as { roles?: RoleOption[] }).roles ?? [],
);

const editPersonalDataModalOpen = ref(false);
const editRoleModalOpen = ref(false);

provide(UserTabContextKey, {
    user,
    editPersonalDataModalOpen,
    editRoleModalOpen,
    roles,
} as UserTabContext);

const activeTab = ref<'personal'>('personal');

const tabs: { key: 'personal'; label: string }[] = [
    { key: 'personal', label: 'Dados pessoais' },
];

function onPersonalDataUpdated() {
    editPersonalDataModalOpen.value = false;
    void router.reload();
}

function onRoleUpdated() {
    editRoleModalOpen.value = false;
    void router.reload();
}
</script>
