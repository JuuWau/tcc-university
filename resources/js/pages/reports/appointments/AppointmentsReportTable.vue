<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { AppointmentsKey } from '@/keys/appointments-report/appointmentsKeys';
import AppointmentStatusBadge from './components/AppointmentReportStatusBadge.vue';

const ctx = inject(AppointmentsKey);

const columnDefs = computed(() => [
    {
        headerName: 'Paciente',
        flex: 2,
        sortable: false,
        valueGetter: (params: any) => {
            const patient = params.data.patient;

            if (!patient) {
                return '—';
            }

            return `${patient.code} - ${patient.name}`;
        },
    },
    {
        headerName: 'Aluno',
        flex: 2,
        sortable: false,
        valueGetter: (params: any) => {
            const student = params.data.student;

            if (!student) {
                return '—';
            }

            return `${student.registration} - ${student.user.person.name}`;
        },
    },
    {
        headerName: 'Responsável',
        flex: 2,
        sortable: false,
        valueGetter: (params: any) => {
            return (
                params.data.slot?.responsibles
                    ?.map((user: any) => user.person?.name)
                    .filter(Boolean)
                    .join(', ') ?? '—'
            );
        },
    },
    {
        headerName: 'Período',
        flex: 1.8,
        sortable: false,
        valueGetter: (params: any) => {
            const period = params.data.enrollment?.slot?.period;

            if (!period) {
                return '—';
            }

            return `${period.academic_year}º Ano - ${period.semester}º Semestre de ${period.calendar_year}`;
        },
    },
    {
        headerName: 'Clínica',
        valueGetter: (params: any) =>
            params.data.slot?.clinic?.name,
        flex: 1.5,
        sortable: false,
    },
    {
        headerName: 'Data',
        field: 'scheduled_start_at',
        flex: 1.2,
        sortable: false,
        valueFormatter: (params: any) => {
            if (!params.value) {
                return '—';
            }

            return new Date(params.value)
                .toLocaleDateString('pt-BR');
        },
    },
    {
        headerName: 'Horário',
        flex: 1,
        sortable: false,
        valueGetter: (params: any) => {
            if (!params.data.scheduled_start_at) {
                return '—';
            }

            const start = new Date(params.data.scheduled_start_at)
                .toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit',
                });

            const end = new Date(params.data.scheduled_end_at)
                .toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit',
                });

            return `${start} - ${end}`;
        },
    },
    {
        headerName: 'Status',
        field: 'status',
        cellRenderer: AppointmentStatusBadge,
        cellRendererParams: (params: any) => ({
            status: params.value,
        }),
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
    <div class="relative mt-4 p-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div
            v-if="ctx.loading.value"
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
        >
            <span class="text-sm text-gray-600">
                Carregando agendamentos...
            </span>
        </div>

        <AgGridVue
            class="ag-theme-alpine"
            style="height: 600px"
            :rowData="ctx.appointments.value"
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
                    @click="ctx.goToPage(ctx.page.value - 1)"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
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
                        @click="ctx.goToPage(p)"
                        :class="[
                            'rounded-md px-3 py-1.5 text-sm transition',
                            p === ctx.page.value
                                ? 'bg-sky-600 text-white shadow'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
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
                    :disabled="ctx.page.value >= ctx.totalPages.value"
                    @click="ctx.goToPage(ctx.page.value + 1)"
                    class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>