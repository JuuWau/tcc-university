<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { ProcedureDeleteKey, ProceduresGroupKey } from '@/keys/procedures/procedureKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { Procedure } from '@/types/procedure';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject(ProcedureDeleteKey);
const procedures = inject(ProceduresGroupKey);
const loading = inject(LoadingKey);

if (!deleteModal) {
    throw new Error('ProcedureDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal!.isOpen.value = false;
}

async function confirmDelete() {
    if (!deleteModal!.procedure.value || loading?.value || !procedures) return;

    try {
        if (loading) loading.value = true;
        await axios.delete(`/procedures/${deleteModal!.procedure.value.id}`);
        procedures.value = procedures.value.filter(
            (p: Procedure) => p.id !== deleteModal!.procedure.value?.id,
        );
        toast.success('Procedimento removido com sucesso');
        close();
    } catch (error: unknown) {
        const err = error as { response?: { data?: { message?: string } } };
        toast.error(err.response?.data?.message ?? 'Erro ao remover procedimento');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="deleteModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold text-red-600">Excluir Procedimento</h2>
            <hr />
            <p class="mb-6 pt-3 text-sm text-gray-600">
                Tem certeza que deseja excluir este procedimento?
                <br />
                Esta ação não poderá ser desfeita.
            </p>
            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <DeleteButton
                    :loading="loading"
                    class="bg-red-600 hover:bg-red-700"
                    @click="confirmDelete"
                >
                    Excluir
                </DeleteButton>
            </div>
        </div>
    </div>
</template>
