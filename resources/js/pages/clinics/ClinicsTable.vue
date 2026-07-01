<script setup lang="ts">
import ClinicTableActionsButtons from '@/components/buttons/ClinicTableActionsButtons.vue';
import CreateButton from '@/components/buttons/CreateButton.vue';
import { ClinicsGroupKey } from '@/keys/clinics/clinicKeys';
import type { Clinic } from '@/types/clinic/clinic';
import { AgGridVue } from 'ag-grid-vue3';
import { computed, inject, ref, type Ref } from 'vue';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', clinic: Clinic): void;
    (e: 'deactivate', clinic: Clinic): void;
    (e: 'activate', clinic: Clinic): void;
    (e: 'delete', clinic: Clinic): void;
}>();

const clinics = inject<Ref<Clinic[]>>(ClinicsGroupKey);
type StatusFilter = 'all' | 'active' | 'inactive';
const activeStatus = ref<StatusFilter>('all');

const statusLabel: Record<StatusFilter, string> = {
    all: 'Todos',
    active: 'Ativas',
    inactive: 'Inativas',
};

const columnDefs = [
    {
        headerName: 'Nome',
        field: 'name',
        flex: 2,
        sortable: true,
        filter: true,
    },
    {
        headerName: 'Status',
        field: 'active',
        sortable: true,
        filter: true,
        cellRenderer: (params: any) => {
            const active = !!params.value;
            const bgClass = active
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-gray-100 text-gray-600';
            const label = active ? 'Ativa' : 'Inativa';
            return `<span class="rounded px-2 py-1 text-xs font-medium ${bgClass}">${label}</span>`;
        },
    },
    {
        headerName: 'Ações',
        field: 'id',
        cellRenderer: ClinicTableActionsButtons,
        cellRendererParams: {
            onEdit: (clinic: Clinic) => emit('edit', clinic),
            onDeactivate: (clinic: Clinic) => emit('deactivate', clinic),
            onActivate: (clinic: Clinic) => emit('activate', clinic),
            onDelete: (clinic: Clinic) => emit('delete', clinic),
        },
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};

function filterByStatus(status: StatusFilter) {
    activeStatus.value = status;
}

const filteredClinics = computed(() => {
    const list = clinics?.value ?? [];
    if (activeStatus.value === 'active')
        return list.filter((clinic) => clinic.active);
    if (activeStatus.value === 'inactive')
        return list.filter((clinic) => !clinic.active);
    return list;
});
</script>

<template>
    <div class="p-6">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                    Clínicas
                </h1>
                <p class="text-sm text-gray-500">
                    Gerencie clínicas/laboratórios para abertura de agenda
                </p>

                <div class="mt-3 inline-flex rounded-full bg-gray-100 p-1">
                    <button
                        v-for="s in [
                            'all',
                            'active',
                            'inactive',
                        ] as StatusFilter[]"
                        :key="s"
                        @click="filterByStatus(s)"
                        class="relative rounded-full px-4 py-1.5 text-sm font-medium transition-all"
                        :class="
                            activeStatus === s
                                ? 'bg-white text-gray-900 shadow'
                                : 'text-gray-500 hover:text-gray-900'
                        "
                    >
                        {{ statusLabel[s] }}
                    </button>
                </div>
            </div>
            <CreateButton
                label="Nova Clínica"
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
                    :rowData="filteredClinics"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :pagination="true"
                    :paginationPageSize="10"
                    :paginationPageSizeSelector="[10, 20, 50, 100]"
                    :localeText="AG_GRID_LOCALE_BR"
                />
            </div>
        </div>
    </div>
</template>
