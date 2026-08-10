<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { StudentsReportKey } from '@/keys/students-report/studentsReportKeys';

const ctx = inject(StudentsReportKey);

if (!ctx) {
    throw new Error(
        'StudentsReportKey não foi fornecido.',
    );
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
        headerName: 'Período',
        flex: 2,
        sortable: false,
        valueGetter: (params: any) => {
            const period = params.data.current_period?.period;

            if (!period) {
                return '—';
            }

            return `${period.academic_year}º ano / ${period.semester}º semestre de ${period.calendar_year}`;
        },
    },
    {
        headerName: 'Status',
        flex: 1,
        sortable: false,
        valueGetter: (params: any) => {
            return params.data.deleted_at
                ? 'Inativo'
                : 'Ativo';
        },
    },
    {
        headerName: 'Convite',
        flex: 1.2,
        sortable: false,
        valueGetter: (params: any) => {
            const invite = params.data.user?.invite;

            if (!invite) {
                return '—';
            }

            return invite.used_at
                ? 'Aceito'
                : 'Pendente';
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

            return new Date(params.value)
                .toLocaleDateString('pt-BR');
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
            class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm"
        >
            <span class="text-sm text-gray-600">
                Carregando estudantes...
            </span>
        </div>

        <AgGridVue
            class="ag-theme-alpine"
            style="height: 600px"
            :rowData="ctx.students.value"
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
                            (p >= ctx.page.value - 2 &&
                                p <= ctx.page.value + 2)
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