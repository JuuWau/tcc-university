<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { RefreshTableKey, UserActivateKey } from '@/keys/users/userKeys';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Ativar colaborador"
                subtitle="Confirme a ativação do colaborador."
            />
            
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600">
                    Tem certeza que deseja ativar o colaborador
                    <strong class="font-semibold text-gray-900">
                        {{
                            activateModal.user.value?.person?.name ??
                            activateModal.user.value?.email
                        }}
                    </strong>
                    ?
                </p>
            </div>
            
            <FormFooter
                :loading="loading"
                action="activate"
                action-label="Ativar"
                @cancel="close"
                @activate="confirmActivate"
            />
        </div>
    </div>
</template>
