<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { inject, type Ref } from 'vue';

import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import {
    SpecialtiesGroup,
    SpecialtiesGroupKey,
} from '@/keys/specialties/specialtyKeys';
import { Specialty } from '@/types/specialty';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
const emit = defineEmits<{
    (e: 'edit', specialty: Specialty): void;
    (e: 'delete', specialty: Specialty): void;
    (e: 'create'): void;
}>();

const specialties = inject<Ref<SpecialtiesGroup>>(SpecialtiesGroupKey);

function openEditModal(id: number) {
    const specialty = specialties.value.find((s: Specialty) => s.id === id);

    if (specialty) {
        emit('edit', specialty);
    }
}

function openDeleteModal(id: number) {
    const specialty = specialties.value.find((s: Specialty) => s.id === id);

    if (specialty) {
        emit('delete', specialty);
    }
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
        headerName: 'Ações',
        field: 'id',
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

function onCellClicked(event: any) {
    const action = event.event.target.dataset?.action;
    const id = Number(event.event.target.dataset?.id);

    if (action === 'edit') {
        const specialty = specialties.value.data.find(
            (s: Specialty) => s.id === id,
        );
        // if (specialty) selectedSpecialty.value = specialty;
        // emit('edit', id);
    }

    if (action === 'delete') {
        emit('delete', id);
    }
}

function goTo(url: string) {
    router.visit(url);
}
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Especialidades
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie as especialidades cadastradas no sistema
                </p>
            </div>

            <CreateButton
                label="Nova Especialidade"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>

        <!-- TABELA -->
        <div class="overflow-x-auto">
            <div
                class="ag-theme-alpine relative rounded-xl border border-gray-200"
                style="height: 520px; width: 100%"
            >
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="specialties"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :localeText="AG_GRID_LOCALE_BR"
                    :pagination="true"
                    :paginationPageSize="10"
                    :paginationPageSizeSelector="[10, 20, 50, 100]"
                    @cell-clicked="onCellClicked"
                />
            </div>
        </div>
    </div>
</template>
