<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { ClinicManagementShowKey, RefreshTableKey } from '@/keys/clinics-management/clinicManagementShowKeys';
import StatusBadgeClinicManagement from '@/components/badges/StatusBadgeClinicManagement.vue';
import { PatientForTab } from '@/types/patient/patient';
import ClinicPatientActionsButtons from '@/components/buttons/ClinicPatientActionsButtons.vue';
import { usePage } from '@inertiajs/vue3';
import ClinicPatientCard from './components/ClinicPatientCard.vue';

const ctx = inject(ClinicManagementShowKey);

const emit = defineEmits([
    'enroll',
]);

const pageData = usePage();

const can = (permission: string) => {
    return pageData.props.auth.permissions.includes(permission);
};

const columnDefs = computed(() => {
    const isWaiting = ctx.activeStatus.value === 'waiting';

    return [
        {
            headerName: 'Código',
            field: 'code',
            flex: 0.8,
            sortable: false,
        },
        {
            headerName: 'Nome',
            field: 'name',
            flex: 2,
            sortable: false,
        },
        {
            headerName: isWaiting ? 'Tempo na fila' : 'Tempo inscrito',
            field: 'enrolled_at',
            flex: 0.8,
            sortable: false,
            valueGetter: (params: any) => {
                if (!params.data?.enrolled_at) {
                    return '—';
                }

                const enrolled = new Date(params.data.enrolled_at);
                const today = new Date();

                const diff = today.getTime() - enrolled.getTime();
                const days = Math.floor(
                    diff / (1000 * 60 * 60 * 24)
                );

                return `${days} ${days === 1 ? 'dia' : 'dias'}`;
            },
        },
        {
            headerName: 'Status',
            colId: 'status',
            flex: 1,
            sortable: false,
            valueGetter: (params: any) =>
                params.data?.status === 'enrolled'
                    ? 'Inscrito'
                    : 'Lista de Espera',
            cellRenderer: StatusBadgeClinicManagement,
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
                isAllowedToEnroll: can('clinics-management.addPatientToWaitingList'),
                isAllowedToRemove: can('clinics-management.removeEnrollmentClinic'),
            },
        },
    ];
});

const fromTo = computed(() => {
    const from =
        (ctx.page.value - 1) * ctx.perPage.value + 1;

    const to = Math.min(
        ctx.page.value * ctx.perPage.value,
        ctx.total.value
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
    <div class="relative mt-4 hidden md:block">
        <div
            v-if="ctx?.loading.value"
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
        >
            <span class="text-sm text-gray-600">
                Carregando pacientes...
            </span>
        </div>

        <AgGridVue
            class="ag-theme-alpine"
            style="height: 550px"
            :rowData="ctx?.patients.value ?? []"
            :columnDefs="columnDefs"
            :defaultColDef="defaultColDef"
            :localeText="AG_GRID_LOCALE_BR"
        />
    </div>
    <div class="mt-4 space-y-3 md:hidden">
        <div
            v-if="ctx?.loading.value"
            class="flex h-40 items-center justify-center text-sm text-gray-500"
        >
            Carregando pacientes...
        </div>

        <template v-else>
            <ClinicPatientCard
                v-for="patient in ctx?.patients.value ?? []"
                :key="patient.id"
                :patient="patient"
                :active-status="ctx.activeStatus.value"
                :is-allowed-to-enroll="can('clinics-management.addPatientToWaitingList')"
                :is-allowed-to-remove="can('clinics-management.removeEnrollmentClinic')"
                @enroll="emit('enroll', $event)"
                @remove="emit('remove', $event)"
            />

            <div
                v-if="!ctx?.patients.value?.length"
                class="rounded-lg border border-gray-200 bg-white py-10 text-center text-sm text-gray-500"
            >
                Nenhum paciente encontrado.
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
                    type="button"
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
</template>