<script setup lang="ts">
import { StudentsPerformanceReportKey } from '@/keys/students-performance-report/studentsPerformanceReportKeys.js';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { AgGridVue } from 'ag-grid-vue3';
import { computed, inject } from 'vue';
import StudentsPerformanceReportCard from './components/StudentsPerformanceReportCard.vue';

const ctx = inject(StudentsPerformanceReportKey);

if (!ctx) {
    throw new Error('StudentsReportKey não foi fornecido.');
}

const columnDefs = computed(() => [
    {
        headerName: 'Nome',
        field: 'person.name',
        flex: 2.5,
        sortable: false,
        valueFormatter: (params: any) => {
            return params.value ?? '—';
        },
    },
    {
        headerName: 'RA',
        field: 'registration',
        flex: 1.4,
        sortable: false,
        valueFormatter: (params: any) => {
            return params.value ?? '—';
        },
    },
    {
        headerName: 'Clínica',
        field: 'clinic_name',
        flex: 1.8,
        sortable: false,
        valueFormatter: (params: any) => {
            return params.value ?? '—';
        },
    },
    {
        headerName: 'Período',
        flex: 2,
        sortable: false,
        valueGetter: (params: any) => {
            const period = params.data;

            if (
                period.academic_year == null ||
                period.semester == null ||
                period.calendar_year == null
            ) {
                return '—';
            }

            return `${period.academic_year}º ano / ${period.semester}º semestre de ${period.calendar_year}`;
        },
    },
    {
        headerName: 'Atendidos',
        field: 'total_appointments',
        flex: 1.2,
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
            return new Date(params.value).toLocaleDateString('pt-BR');
        },
    },
]);

const fromTo = computed(() => {
    const from = (ctx.page.value - 1) * ctx.perPage.value + 1;

    const to = Math.min(ctx.page.value * ctx.perPage.value, ctx.total.value);

    return ctx.total.value ? `${from}-${to} de ${ctx.total.value}` : '0';
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
                Carregando estudantes...
            </span>
        </div>

        <div class="relative mt-4 hidden md:block">
            <AgGridVue
                class="ag-theme-alpine"
                style="height: 600px"
                :rowData="ctx.students.value"
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
                Carregando estudantes...
            </div>

            <template v-else>
                <StudentsPerformanceReportCard
                    v-for="student in ctx.students.value"
                    :key="student.id"
                    :student="student"
                />

                <div
                    v-if="!ctx.students.value.length"
                    class="rounded-lg border border-gray-200 bg-white py-10 text-center text-sm text-gray-500"
                >
                    Nenhum estudante encontrado.
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
                    @click="ctx.goToPage(ctx.page.value - 1)"
                >
                    Anterior
                </button>

                <template v-for="p in ctx.totalPages.value" :key="p">
                    <button
                        v-if="
                            p === 1 ||
                            p === ctx.totalPages.value ||
                            (p >= ctx.page.value - 2 && p <= ctx.page.value + 2)
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
                            p === ctx.page.value - 3 || p === ctx.page.value + 3
                        "
                        class="px-1 text-gray-500"
                    >
                        …
                    </span>
                </template>

                <button
                    type="button"
                    :disabled="ctx.page.value >= ctx.totalPages.value"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                    @click="ctx.goToPage(ctx.page.value + 1)"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>
