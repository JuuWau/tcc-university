<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import { UserActivateKey, RefreshTableKey } from '@/keys/users/userKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import ActivationButton from '@/components/buttons/ActivationButton.vue';

const activateModal = inject(UserActivateKey);
const refreshTableRef = inject(RefreshTableKey);
const loading = inject(LoadingKey);

if (!activateModal || !loading) {
    throw new Error('UserActivateModal precisa estar dentro do provider');
}

function close() {
    activateModal.isOpen.value = false;
    activateModal.user.value = null;
}

async function confirmActivate() {
    const user = activateModal.user.value;
    if (!user || loading.value) return;

    try {
        loading.value = true;
        await axios.delete(`/users/activate/${user.id}`);
        toast.success('Colaborador ativado com sucesso');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao ativar colaborador',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="activateModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-bold text-gray-800">
                Ativar colaborador
            </h2>
            <hr />

            <p class="pt-4 text-sm text-gray-600">
                Tem certeza que deseja ativar o colaborador
                <strong>{{
                    activateModal.user.value?.person?.name ??
                    activateModal.user.value?.email
                }}</strong
                >?
            </p>

            <div class="flex justify-end gap-2 pt-6">
                <CancelButton @click="close" />
                <ActivationButton
                    type="button"
                    :disabled="loading"
                    @click="confirmActivate"
                >
                    {{ loading ? 'Ativando...' : 'Ativar' }}
                </ActivationButton>
            </div>
        </div>
    </div>
</template>
