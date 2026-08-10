<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { PatientsReportKey } from '@/keys/patients-report/patientsReportKeys';
import PatientStatusBadge from '../../../components/badges/PatientsReportStatusBadge.vue';

const ctx = inject(PatientsReportKey);

const columnDefs = computed(() => [
    {
        headerName: 'Nome',
        flex: 2,
        sortable: false,
        valueGetter: (params: any) => {
            return params.data.name ?? '—';
        },
    },
    {
        headerName: 'Código',
        flex: 1.2,
        sortable: false,
        valueGetter: (params: any) => {
            return params.data.code ?? '—';
        },
    },
    {
        headerName: 'CPF',
        flex: 1.5,
        sortable: false,
        valueGetter: (params: any) => {
            return params.data.cpf ?? '—';
        },
    },
    {
        headerName: 'Telefone',
        flex: 1.5,
        sortable: false,
        valueGetter: (params: any) => {
            return params.data.phone ?? '—';
        },
    },
    {
        headerName: 'Data de nascimento',
        flex: 1.5,
        sortable: false,
        valueGetter: (params: any) => {
            if (!params.data.birth_date) {
                return '—';
            }

            return new Date(
                `${params.data.birth_date}T00:00:00`
            ).toLocaleDateString('pt-BR');
        },
    },
    {
        headerName: 'Tipo',
        flex: 1.2,
        sortable: false,
        valueGetter: (params: any) => {
            const types: Record<string, string> = {
                pediatria: 'Pediatria',
                adulto: 'Adulto',
            };

            return types[params.data.patient_type]
                ?? params.data.patient_type
                ?? '—';
        },
    },
    {
        headerName: 'Status',
        flex: 1.5,
        sortable: false,
        cellRenderer: PatientStatusBadge,
        cellRendererParams: (params: any) => ({
            status: params.value,
        }),
        valueGetter: (params: any) => {
            return params.data.status;
        },
    },
    {
        headerName: 'Cadastro',
        flex: 1.5,
        sortable: false,
        valueGetter: (params: any) => {
            if (!params.data.created_at) {
                return '—';
            }

            return new Date(
                params.data.created_at
            ).toLocaleDateString('pt-BR');
        },
    },
]);

const fromTo = computed(() => {
    const from =
        (ctx.page.value - 1) * ctx.perPage.value + 1;

    const to = Math.min(
        ctx.page.value * ctx.perPage.value,
        ctx.total.value,
    );

    return ctx.total.value
        ? `${from}-${to} de ${ctx.total.value}`
        : '0';
});

const defaultColDef = {
    resizable: true,
};
</script>

<template>
    <div
        class="relative mt-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    >
        <div
            v-if="ctx.loading.value"
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
        >
            <span class="text-sm text-gray-600">
                Carregando pacientes...
            </span>
        </div>

        <AgGridVue
            class="ag-theme-alpine"
            style="height: 600px"
            :rowData="ctx.patients.value"
            :columnDefs="columnDefs"
            :defaultColDef="defaultColDef"
            :localeText="AG_GRID_LOCALE_BR"
        />

        <div
            v-if="ctx.totalPages.value > 0"
            class="mt-4 flex flex-wrap items-center justify-between gap-2"
        >
            <p class="text-sm text-gray-600">
                {{ fromTo }}
            </p>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    :disabled="ctx.page.value <= 1"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                    @click="ctx.goToPage(ctx.page.value - 1)"
                >
                    Anterior
                </button>

                <template
                    v-for="p in ctx.totalPages.value"
                    :key="p"
                >
                    <button
                        v-if="
                            p === 1 ||
                            p === ctx.totalPages.value ||
                            (
                                p >= ctx.page.value - 2 &&
                                p <= ctx.page.value + 2
                            )
                        "
                        type="button"
                        :class="[
                            'rounded-md px-3 py-1.5 text-sm transition',
                            p === ctx.page.value
                                ? 'bg-sky-600 text-white shadow'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
                        @click="ctx.goToPage(p)"
                    >
                        {{ p }}
                    </button>

                    <span
                        v-else-if="
                            p === ctx.page.value - 3 ||
                            p === ctx.page.value + 3
                        "
                        class="px-1 text-gray-500"
                    >
                        …
                    </span>
                </template>

                <button
                    type="button"
                    :disabled="
                        ctx.page.value >= ctx.totalPages.value
                    "
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                    @click="ctx.goToPage(ctx.page.value + 1)"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>