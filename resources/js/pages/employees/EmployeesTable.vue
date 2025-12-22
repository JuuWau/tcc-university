<script setup lang="ts">
import { EmployeesKey } from '@/keys/employees/employeesKey';
import { router } from '@inertiajs/vue3';
import { AgGridVue } from 'ag-grid-vue3';
import { inject } from 'vue';

const emit = defineEmits(['edit', 'delete']);

const employees = inject(EmployeesKey);

if (!employees) {
    throw new Error('Employees not provided');
}

const columnDefs = [
    {
        headerName: 'Foto',
        field: 'photo',
        width: 100,
        cellRenderer: (params: any) => {
            if (params.value) {
                return `<img src="/storage/${params.value}" class="w-10 h-10 rounded-full object-cover"/>`;
            } else {
                return `<div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <i class="fa-solid fa-user text-gray-500"></i>
                    </div>`;
            }
        },
    },
    {
        headerName: 'Nome',
        field: 'name',
        sortable: true,
        filter: true,
        flex: 2,
    },
    { headerName: 'Email', field: 'email', sortable: true, filter: true },
    {
        headerName: 'Permissão',
        field: 'permission_type',
        cellRenderer: (params: any) => {
            return `<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-lg">${params.value}</span>`;
        },
    },
    {
        headerName: 'Ações',
        field: 'id',
        width: 200,
        cellRenderer: (params: any) => {
            return `
            <button class="bg-blue-500 text-white px-2 rounded-lg mr-2 hover:bg-blue-600"
            data-action="edit" data-id="${params.value}">
            <i class="fas fa-user-edit"></i> Editar
            </button>
            <button class="bg-red-500 text-white px-2 rounded-lg hover:bg-red-600"
            data-action="delete" data-id="${params.value}">
            <i class="fas fa-trash"></i> Deletar
            </button>
        `;
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};

function onCellClicked(event: any) {
    if (event.event.target.dataset?.action === 'edit') {
        emit('edit', Number(event.event.target.dataset.id));
    }
    if (event.event.target.dataset?.action === 'delete') {
        emit('delete', Number(event.event.target.dataset.id));
    }
}

function goTo(url: string) {
    router.visit(url);
}
</script>

<template>
    <div class="p-6">
        <h1 class="mb-4 text-xl font-bold">Funcionários</h1>
        <div class="overflow-x-auto">
            <div class="ag-theme-alpine" style="height: 500px; width: 100%">
                <ag-grid-vue
                    class="ag-theme-alpine h-full"
                    :rowData="employees.data"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    @cell-clicked="onCellClicked"
                />
            </div>
        </div>
        <div class="mt-4 flex justify-center space-x-2">
            <button
                v-if="employees.prev_page_url"
                @click="goTo(employees.prev_page_url)"
                class="rounded bg-gray-200 px-3 py-1 hover:bg-gray-300"
            >
                Anterior
            </button>
            <button
                v-if="employees.next_page_url"
                @click="goTo(employees.next_page_url)"
                class="rounded bg-gray-200 px-3 py-1 hover:bg-gray-300"
            >
                Próxima
            </button>
        </div>
    </div>
</template>
