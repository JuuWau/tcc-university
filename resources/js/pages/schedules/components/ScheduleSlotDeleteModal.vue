<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import DeleteButton from '@/components/buttons/DeleteButton.vue';
import { ScheduleSlotDeleteKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { inject } from 'vue';
import { toast } from 'vue3-toastify';
import { formatDateBr } from '@/src/utils/formatters';

const deleteModal = inject(ScheduleSlotDeleteKey);
const loading = inject(LoadingKey);

if (!deleteModal) {
    throw new Error('ScheduleSlotDeleteModal precisa estar dentro do provider');
}

function close() {
    deleteModal.isOpen.value = false;
}

async function submit() {
    const row = deleteModal.row.value;
    if (!row || loading?.value) return;

    try {
        if (loading) loading.value = true;
        await axios.delete(`/schedules/slots/${row.id}`);
        toast.success('Agenda excluída com sucesso');
        close();
        router.reload({ preserveScroll: true });
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao excluir agenda');
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
            <h2 class="text-lg font-bold text-red-600">Excluir agenda</h2>
            <hr />

            <span class="mb-6 text-sm text-gray-600 leading-relaxed">
                Tem certeza que deseja excluir o horário
                <strong>
                    {{
                        deleteModal.row?.value?.date
                            ? formatDateBr(deleteModal.row.value.date)
                            : ''
                    }}
                </strong>

                (
                <strong>
                    {{ deleteModal.row?.value?.start_time?.slice(0, 5) }} às
                    {{ deleteModal.row?.value?.end_time?.slice(0, 5) }}
                </strong>
                )?

                <br />

                Esta ação é irreversível e irá:
            </span>

            <ul class="mb-6 list-disc pl-5 text-sm text-gray-600 space-y-1">
                <li>Remover o slot de agenda</li>
                <li>Cancelar todas as inscrições dos alunos</li>
                <li>Cancelar todos os agendamentos vinculados a esses alunos</li>
            </ul>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <DeleteButton :loading="loading" @click="submit">
                    Excluir
                </DeleteButton>
            </div>
        </div>
    </div>
</template>
