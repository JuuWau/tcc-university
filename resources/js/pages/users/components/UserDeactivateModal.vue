<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { RefreshTableKey, UserDeactivateKey } from '@/keys/users/userKeys';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deactivateModal = inject(UserDeactivateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!deactivateModal || !loading) {
    throw new Error('UserDeactivateModal precisa estar dentro do provider');
}

function close() {
    deactivateModal.isOpen.value = false;
    deactivateModal.user.value = null;
}

async function confirmDeactivate() {
    const user = deactivateModal.user.value;
    if (!user || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/users/deactivate/${user.id}`);
        toast.success('Colaborador inativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao inativar colaborador',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="deactivateModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-bold text-gray-800">
                Inativar colaborador
            </h2>
            <hr />

            <p class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja inativar o colaborador
                <strong>{{
                    deactivateModal.user.value?.person?.name ??
                    deactivateModal.user.value?.email
                }}</strong
                >?
            </p>

            <div class="flex justify-end gap-2 pt-6">
                <CancelButton @click="close" />
                <button
                    type="button"
                    :disabled="loading"
                    class="flex items-center gap-2 rounded bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700 disabled:opacity-60"
                    @click="confirmDeactivate"
                >
                    {{ loading ? 'Inativando...' : 'Inativar' }}
                </button>
            </div>
        </div>
    </div>
</template>
