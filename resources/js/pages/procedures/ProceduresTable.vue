<script setup lang="ts">
import { computed, inject, type Ref } from 'vue';
import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { ProceduresGroupKey } from '@/keys/procedures/procedureKeys';
import type { Procedure } from '@/types/procedure';
import { AgGridVue } from 'ag-grid-vue3';

const emit = defineEmits<{
    (e: 'edit', procedure: Procedure): void;
    (e: 'delete', procedure: Procedure): void;
    (e: 'create'): void;
}>();

const proceduresRef = inject<Ref<Procedure[]>>(ProceduresGroupKey);
const rowData = computed(() => proceduresRef?.value ?? []);

function openEditModal(id: number) {
    const procedure = proceduresRef?.value?.find((p: Procedure) => p.id === id);
    if (procedure) emit('edit', procedure);
}

function openDeleteModal(id: number) {
    const procedure = proceduresRef?.value?.find((p: Procedure) => p.id === id);
    if (procedure) emit('delete', procedure);
}

const columnDefs = [
    {
        headerName: 'Nome',
        field: 'name',
        flex: 2,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Especialidade',
        field: 'specialty',
        flex: 2,
        sortable: true,
        filter: true,
        valueFormatter: (params: { value?: { name?: string } }) =>
            params.value?.name ?? '—',
    },
    {
        headerName: 'Ações',
        field: 'id',
        flex: 1,
        cellRenderer: ActionsButtons,
        cellRendererParams: {
            onEdit: (id: number) => openEditModal(id),
            onDelete: (id: number) => openDeleteModal(id),
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Procedimentos
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie os procedimentos por especialidade
                </p>
            </div>
            <CreateButton
                label="Novo Procedimento"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>
        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative rounded-xl border border-gray-200"
                style="height: 520px; width: 100%"
            >
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :row-data="rowData"
                    :column-defs="columnDefs"
                    :default-col-def="defaultColDef"
                />
            </div>
        </div>
    </div>
</template>
