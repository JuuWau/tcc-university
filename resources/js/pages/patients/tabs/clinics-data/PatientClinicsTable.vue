<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { PatientClinicsKey } from '@/keys/patients/patientClinicsKeys';
import StatusBadgeClinicManagement from '@/components/badges/StatusBadgeClinicManagement.vue';
import ClinicPatientActionsButtons from '@/components/buttons/ClinicPatientActionsButtons.vue';
import { PatientForTab } from '@/types/patient/patient';
import { formatDateBr } from '@/src/utils/formatters';

const ctx = inject(PatientClinicsKey);

const emit = defineEmits([
    'enroll',
]);

const columnDefs = computed(() => {
    const isWaiting =
        ctx?.activeStatus.value === 'waiting';

    return [
        {
            headerName: 'Clínica',
            field: 'clinic_name',
            flex: 2,
            sortable: false,
        },
        {
                headerName: 'Data de entrada',
                flex: 2,
                sortable: false,
                valueGetter: (params: any) => {
                        const date = params.data?.created_at;

                        return date
                        ? formatDateBr(date)
                        : '—';
                },
        },
        {
            headerName: isWaiting
                ? 'Tempo na fila'
                : 'Tempo inscrito',
            field: 'joined_at',
            flex: 1,
            sortable: false,
            valueGetter: (params: any) => {
                const date =
                    params.data?.joined_at ??
                    params.data?.enrolled_at;

                if (!date) {
                    return '—';
                }

                const startDate = new Date(date);
                const today = new Date();

                const diff =
                    today.getTime() -
                    startDate.getTime();

                const days = Math.floor(
                    diff /
                        (1000 * 60 * 60 * 24),
                );

                return `${days} ${
                    days === 1
                        ? 'dia'
                        : 'dias'
                }`;
            },
        },
        {
            headerName: 'Status',
            field: 'status',
            flex: 1,
            sortable: false,
            valueGetter: (params: any) =>
                params.data?.status ===
                'enrolled'
                    ? 'Inscrito'
                    : 'Lista de Espera',
            cellRenderer:
                StatusBadgeClinicManagement,
        },
        {
            headerName: 'Ações',
            colId: 'actions',
            cellClass: 'cell-center',
            cellRenderer: ClinicPatientActionsButtons,
            cellRendererParams: {
                action: isWaiting ? 'enroll' : 'remove',
                onEnroll: (patient: PatientForTab) =>
                    emit('enroll', patient),
                onRemove: (patient: PatientForTab) =>
                    emit('remove', patient),
            },
        },
    ];
});

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
    <div class="relative mt-4">
        <div
            v-if="ctx?.loading.value"
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
        >
            <span
                class="text-sm text-gray-600"
            >
                Carregando clínicas...
            </span>
        </div>

        <AgGridVue
            class="ag-theme-alpine"
            style="height: 550px"
            :rowData="
                ctx?.clinics.value ?? []
            "
            :columnDefs="columnDefs"
            :defaultColDef="
                defaultColDef
            "
            :localeText="
                AG_GRID_LOCALE_BR
            "
        />
    </div>

    <div
        v-if="
            ctx &&
            ctx.totalPages.value > 0
        "
        class="mt-4 flex flex-wrap items-center justify-between gap-2"
    >
        <p
            class="text-sm text-gray-600"
        >
            {{ fromTo }}
        </p>

        <div
            class="flex items-center gap-1"
        >
            <button
                type="button"
                :disabled="
                    ctx.page.value <= 1
                "
                @click="
                    ctx.goToPage(
                        ctx.page.value - 1,
                    )
                "
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
                        p ===
                            ctx.totalPages
                                .value ||
                        (p >=
                            ctx.page.value -
                                2 &&
                            p <=
                                ctx.page
                                    .value +
                                    2)
                    "
                    type="button"
                    @click="
                        ctx.goToPage(p)
                    "
                    :class="[
                        'rounded-md px-3 py-1.5 text-sm transition',
                        p ===
                        ctx.page.value
                            ? 'bg-sky-600 text-white shadow'
                            : 'text-gray-600 hover:bg-gray-100',
                    ]"
                >
                    {{ p }}
                </button>

                <span
                    v-else-if="
                        p ===
                            ctx.page
                                .value -
                                3 ||
                        p ===
                            ctx.page
                                .value +
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
                @click="
                    ctx.goToPage(
                        ctx.page.value + 1,
                    )
                "
                class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
            >
                Próxima
            </button>
        </div>
    </div>
</template>