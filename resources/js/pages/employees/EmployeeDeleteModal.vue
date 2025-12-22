<template>
    <TransitionRoot :show="isOpen" as="template">
        <Dialog as="div" class="relative z-10" @close="close">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 transition-opacity"></div>
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 text-left align-middle text-gray-700 shadow-xl transition-all"
                        >
                            <DialogTitle class="mb-3 text-lg font-semibold text-gray-700"> Confirmar Exclusão </DialogTitle>

                            <p class="mb-6 text-gray-600">
                                Tem certeza que deseja excluir o funcionário
                                <span class="font-semibold text-red-600">{{ employeeName }}</span
                                >? Essa ação não poderá ser desfeita.
                            </p>

                            <div class="flex justify-end gap-3">
                                <button type="button" class="rounded bg-gray-200 px-4 py-2 hover:bg-gray-300" @click="close">Cancelar</button>
                                <button type="button" class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700" @click="confirmDelete">
                                    Excluir
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { SelectedEmployeeKey } from '@/keys/employees/selectedEmployeeKey';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import axios from 'axios';
import { computed, inject, ref } from 'vue';
import { toast } from 'vue3-toastify';

const isOpen = ref(false);
const selectedEmployee = inject(SelectedEmployeeKey) as any;

const employeeName = computed(() => selectedEmployee?.value?.name || '');

const emit = defineEmits(['reload-table']);

function open() {
    isOpen.value = true;
}

function close() {
    isOpen.value = false;
}

async function confirmDelete() {
    if (!selectedEmployee?.value?.id) return;

    try {
        let res = await axios.delete(`/employees/${selectedEmployee.value.id}`);
        toast.success(res.data.message || 'Funcionário deletado com sucesso!');
        close();
        emit('reload-table');
    } catch (error) {
        toast.error('Erro ao deletar funcionário:');
    }
}

defineExpose({ open, close });
</script>
