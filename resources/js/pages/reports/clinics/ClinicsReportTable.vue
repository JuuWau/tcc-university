<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { ClinicsReportKey } from '@/keys/clinics-report/clinicsReportKeys';
import ClinicsReportCard from './components/ClinicsReportCard.vue';

const ctx = inject(ClinicsReportKey);

if (!ctx) {
    throw new Error(
        'ClinicsReportKey não foi fornecido.',
    );
}

const columnDefs = computed(() => [
    {
        headerName: 'Clínica',
        field: 'name',
        flex: 2.5,
        sortable: false,

        valueFormatter: (params: any) => {
            return params.value ?? '—';
        },
    },

    {
        headerName: 'Status',
        flex: 1,
        sortable: false,

        valueGetter: (params: any) => {
            return params.data.active
                ? 'Ativa'
                : 'Inativa';
        },
    },

    {
        headerName: 'Horários',
        field: 'schedule_slots_count',
        flex: 1,
        sortable: false,

        valueFormatter: (params: any) => {
            return params.value ?? 0;
        },
    },

    {
        headerName: 'Vagas',
        field: 'available_slots_sum',
        flex: 1,
        sortable: false,

        valueFormatter: (params: any) => {
            return params.value ?? 0;
        },
    },

    {
        headerName: 'Cadastro',
        field: 'created_at',
        flex: 1.4,
        sortable: false,

        valueFormatter: (params: any) => {
            if (!params.value) {
                return '—';
            }

            return new Date(
                params.value,
            ).toLocaleDateString('pt-BR');
        },
    },
]);

const fromTo = computed(() => {
    const from =
        (ctx!.page.value - 1) *
            ctx!.perPage.value +
        1;

    const to = Math.min(
        ctx!.page.value *
            ctx!.perPage.value,
        ctx!.total.value,
    );

    return ctx!.total.value
        ? `${from}-${to} de ${ctx!.total.value}`
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
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm"
        >
            <span class="text-sm text-gray-600">
                Carregando clínicas...
            </span>
        </div>

        <div class="relative mt-4 hidden md:block">
            <AgGridVue
                class="ag-theme-alpine"
                style="height: 600px"
                :rowData="ctx.clinics.value"
                :columnDefs="columnDefs"
                :defaultColDef="defaultColDef"
                :localeText="AG_GRID_LOCALE_BR"
            />
        </div>

        <div class="mt-4 space-y-3 md:hidden">
            <div
                v-if="ctx.loading.value"
                class="flex h-40 items-center justify-center text-sm text-gray-500"
            >
                Carregando clínicas...
            </div>

            <template v-else>
                <ClinicsReportCard
                    v-for="clinic in ctx.clinics.value"
                    :key="clinic.id"
                    :clinic="clinic"
                />

                <div
                    v-if="!ctx.clinics.value.length"
                    class="rounded-lg border border-gray-200 bg-white py-10 text-center text-sm text-gray-500"
                >
                    Nenhuma clínica encontrada.
                </div>
            </template>
        </div>

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
                    @click="
                        ctx.goToPage(
                            ctx.page.value - 1,
                        )
                    "
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
                            p ===
                                ctx.totalPages
                                    .value ||
                            (p >=
                                ctx.page.value -
                                    2 &&
                                p <=
                                    ctx.page.value +
                                        2)
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
                            p ===
                                ctx.page.value -
                                    3 ||
                            p ===
                                ctx.page.value +
                                    3
                        "
                        class="px-1 text-gray-500"
                    >
                        …
                    </span>
                </template>

                <button
                    type="button"
                    :disabled="
                        ctx.page.value >=
                        ctx.totalPages.value
                    "
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                    @click="
                        ctx.goToPage(
                            ctx.page.value + 1,
                        )
                    "
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>