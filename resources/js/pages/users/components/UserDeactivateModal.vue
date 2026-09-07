<script setup lang="ts">
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Inativar colaborador"
                subtitle="Confirme a inativação do colaborador."
            />
            
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600">
                    Tem certeza que deseja inativar o colaborador
                    <strong class="font-semibold text-gray-900">
                        {{
                            deactivateModal.user.value?.person?.name ??
                            deactivateModal.user.value?.email
                        }}
                    </strong>
                    ?
                </p>
            </div>

            <FormFooter
                :loading="loading"
                action="deactivate"
                action-label="Inativar"
                @cancel="close"
                @deactivate="confirmDeactivate"
            />
        </div>
    </div>
</template>
