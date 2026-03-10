<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <UserCreateModal />
                        <UserDeactivateModal />
                        <UserDeleteModal />
                        <UserActivateModal />

                        <UsersTable
                            @create="openCreateModal"
                            @resend="resendInvite"
                            @view="openViewModal"
                            @deactivate="openDeactivateModal"
                            @activate="openActivateModal"
                            @delete="openDeleteModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { LoadingKey } from '@/keys/ui/loadingKey';
import {
    RefreshTableKey,
    UserActivateKey,
    UserCreateKey,
    UserDeactivateKey,
    UserDeleteKey,
} from '@/keys/users/userKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import UserActivateModal from '@/pages/users/components/UserActivateModal.vue';
import UserCreateModal from '@/pages/users/components/UserCreateModal.vue';
import UserDeactivateModal from '@/pages/users/components/UserDeactivateModal.vue';
import UserDeleteModal from '@/pages/users/components/UserDeleteModal.vue';
import UsersTable from '@/pages/users/UsersTable.vue';
import type { UserWithInvite } from '@/types/user/user';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { provide, ref } from 'vue';
import { toast } from 'vue3-toastify';

const loading = ref(false);
const refreshTableRef = ref<(() => void) | null>(null);

const createModal = { isOpen: ref(false) };
const deactivateModal = {
    isOpen: ref(false),
    user: ref<UserWithInvite | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    user: ref<UserWithInvite | null>(null),
};
const activateModal = {
    isOpen: ref(false),
    user: ref<UserWithInvite | null>(null),
};

provide(RefreshTableKey, refreshTableRef);
provide(UserCreateKey, createModal);
provide(UserDeactivateKey, deactivateModal);
provide(UserDeleteKey, deleteModal);
provide(UserActivateKey, activateModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openViewModal(user: UserWithInvite) {
    router.visit(`/users/${user.id}`);
}

function openDeactivateModal(user: UserWithInvite) {
    deactivateModal.user.value = user;
    deactivateModal.isOpen.value = true;
}

function openDeleteModal(user: UserWithInvite) {
    deleteModal.user.value = user;
    deleteModal.isOpen.value = true;
}

function openActivateModal(user: UserWithInvite) {
    activateModal.user.value = user;
    activateModal.isOpen.value = true;
}

async function resendInvite(user: UserWithInvite) {
    if (loading.value) return;

    try {
        loading.value = true;
        await axios.post(`/users/resend-invite/${user.id}`);
        toast.success('Convite reenviado com sucesso');
        refreshTableRef.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao reenviar convite',
        );
    } finally {
        loading.value = false;
    }
}
</script>
