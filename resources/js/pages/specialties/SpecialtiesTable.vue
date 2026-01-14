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
            class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <h1 class="text-lg font-bold sm:text-xl">Especialidades</h1>

            <CreateButton
                label="Nova Especialidade"
                icon="Plus"
                class="w-full sm:w-auto"
                @click="$emit('create')"
            />
        </div>
        <div class="overflow-x-auto">
            <div class="ag-theme-alpine" style="height: 500px; width: 100%">
                <AgGridVue
                    class="ag-theme-alpine h-full"
                    :rowData="specialties"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    @cell-clicked="onCellClicked"
                />
            </div>
        </div>

        <!-- <div class="mt-4 flex justify-center space-x-2">
            <button
                v-if="specialties.prev_page_url"
                @click="goTo(specialties.prev_page_url)"
                class="rounded bg-gray-200 px-3 py-1 hover:bg-gray-300"
            >
                Anterior
            </button>
            <button
                v-if="specialties.next_page_url"
                @click="goTo(specialties.next_page_url)"
                class="rounded bg-gray-200 px-3 py-1 hover:bg-gray-300"
            >
                Próxima
            </button>
        </div> -->
    </div>
</template>
