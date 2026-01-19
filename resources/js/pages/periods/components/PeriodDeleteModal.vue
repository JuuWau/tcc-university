<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { PeriodDeleteKey, PeriodsGroupKey } from '@/keys/periods/periodKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { Period } from '@/types/period';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';

const deleteModal = inject<any>(PeriodDeleteKey);
const periods = inject<any>(PeriodsGroupKey);
const loading = inject(LoadingKey);

if (!deleteModal || !periods || !loading) {
    throw new Error('PeriodDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function confirmDelete() {
    if (!deleteModal.period.value || loading.value) return;

    try {
        loading.value = true;

        await axios.delete(`/periods/${deleteModal.period.value.id}`);

        periods.value = periods.value.filter(
            (p: Period) => p.id !== deleteModal.period.value.id,
        );

        toast.success('Período removido com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao remover período');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="deleteModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-2 text-lg font-bold text-red-600">Excluir Período</h2>
            <hr />

            <p class="mb-6 pt-3 text-sm text-gray-600">
                Tem certeza que deseja excluir este período?
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
