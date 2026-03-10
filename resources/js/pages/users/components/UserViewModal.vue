<script setup lang="ts">
import { UserViewKey } from '@/keys/users/userKeys';
import type { UserWithInvite } from '@/types/user/user';
import { inject } from 'vue';

const viewModal = inject(UserViewKey);

if (!viewModal) {
    throw new Error('UserViewModal precisa estar dentro do provider');
}

const user = viewModal.user;

function close() {
    viewModal.isOpen.value = false;
    viewModal.user.value = null;
}

function displayName(u: UserWithInvite | null): string {
    if (!u) return '—';
    return u.person?.name ?? u.email ?? '—';
}
</script>

<template>
    <div
        v-if="viewModal.isOpen.value && user.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="close"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">
                    Dados do colaborador
                </h2>
                <button
                    type="button"
                    class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                    aria-label="Fechar"
                    @click="close"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
            <hr class="mb-4" />

            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Nome</dt>
                    <dd class="mt-0.5 text-gray-900">
                        {{ displayName(user.value) }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Email</dt>
                    <dd class="mt-0.5 text-gray-900">
                        {{ user.value?.email ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Perfil</dt>
                    <dd class="mt-0.5 text-gray-900">
                        {{ user.value?.role?.name ?? '—' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    class="rounded bg-slate-200 px-4 py-1.5 hover:bg-slate-300"
                    @click="close"
                >
                    Fechar
                </button>
            </div>
        </div>
    </div>
</template>
