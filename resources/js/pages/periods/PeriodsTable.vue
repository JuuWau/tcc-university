<script setup lang="ts">
import { inject, type Ref } from 'vue';

import ActionsButtons from '@/components/buttons/ActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { PeriodsGroup, PeriodsGroupKey } from '@/keys/periods/periodKeys';
import { Period } from '@/types/period';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
const emit = defineEmits<{
    (e: 'edit', period: Period): void;
    (e: 'delete', period: Period): void;
    (e: 'create'): void;
}>();

const periods = inject<Ref<PeriodsGroup>>(PeriodsGroupKey);

function openEditModal(id: number) {
    const period = periods.value.find((s: Period) => s.id === id);

    if (period) {
        emit('edit', period);
    }
}

function openDeleteModal(id: number) {
    const period = periods.value.find((s: Period) => s.id === id);

    if (period) {
        emit('delete', period);
    }
}

const columnDefs = [
    {
        headerName: 'Ano Acadêmico',
        field: 'academic_year',
        flex: 1,
        sortable: true,
        filter: true,
        valueFormatter: (params: any) => {
            if (!params.value) return '';
            return `${params.value}º ano`;
        },
    },
    {
        headerName: 'Semestre',
        field: 'semester',
        flex: 1,
        sortable: true,
        filter: true,
        valueFormatter: (params: any) => {
            if (!params.value) return '';
            return `${params.value}º semestre`;
        },
    },
    {
        headerName: 'Ano Calendário',
        field: 'calendar_year',
        flex: 1,
        sortable: true,
        filter: true,
        valueFormatter: (params: any) => {
            const value = Number(params.value);
            if (!value) return '';
            return `${value}`;
        },
    },
    {
        headerName: 'Especialidades',
        field: 'specialties',
        autoHeight: true,
        flex: 2,
        cellClass: 'cell-center',
        sortable: true,
        filter: true,
        cellRenderer: (params) => {
            if (!params.value?.length) return '';

            return `
            <div class="flex flex-wrap items-center h-full gap-1">
                ${params.value
                    .map(
                        (s: any) => `
                    <span class="px-2 py-0.5 text-xs font-medium
                                rounded-xl
                                bg-sky-100 text-sky-700">
                        ${s.name}
                    </span>
                    `,
                    )
                    .join('')}
            </div>
            `;
        },
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
        const period = periods.value.data.find((s: Period) => s.id === id);
        // if (period) selectedPeriod.value = period;
        // emit('edit', id);
    }

    if (action === 'delete') {
        emit('delete', id);
    }
}
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Períodos
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie anos acadêmicos, semestres e especialidades
                </p>
            </div>

            <CreateButton
                label="Novo Período"
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
                    :rowData="periods"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    @cell-clicked="onCellClicked"
                    :pagination="true"
                    :paginationPageSize="10"
                    :paginationPageSizeSelector="[10, 20, 50, 100]"
                    :localeText="AG_GRID_LOCALE_BR"
                />
            </div>
        </div>
    </div>
</template>
