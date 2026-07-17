<script setup lang="ts">
import { inject, computed } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import {AppointmentsConfirmationKey } from '@/keys/appointments-confirmation/appointmentsConfirmationKeys';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import { formatDateBr } from '@/src/utils/formatters';
import StatusBadgeAppointment from '@/components/badges/StatusBadgeAppointment.vue';
import WhatsappButton from '@/components/buttons/WhatsappButton.vue';
import AppointmentActions from '@/components/buttons/AppointmentActions.vue';

const context = inject(
    AppointmentsConfirmationKey
)!;

const appointments = computed(
    () => context.appointments.value
);

const {
    updateAppointmentStatus,
} = context;

async function confirmAppointment(row: any) {
    await context.updateAppointmentStatus(
        row.id,
        'confirmed'
    );
}

async function cancelAppointment(row: any) {
    await context.updateAppointmentStatus(
        row.id,
        'canceled'
    );
}

async function scheduleAppointment(row: any) {
    await context.updateAppointmentStatus(
        row.id,
        'scheduled'
    );
}

const hasAppointments = computed(
    () => appointments.value.length > 0
);

function whatsappMessage(appointment: any) {
    return `Olá ${appointment.patient?.name} - ${appointment.patient?.code},

Seu atendimento está agendado para ${formatDateBr(appointment.scheduled_start_at)} às ${appointment.scheduled_start_at.substring(11, 16)}.

Podemos confirmar sua presença?`;
}

const columnDefs = [
    {
        headerName: 'Data e Horário',
        flex: 0.6,

        cellRenderer: (params: any) => {
            const date = formatDateBr(
                params.data.scheduled_start_at
            );

            const startTime = params.data.scheduled_start_at.substring(11, 16);

            const endTime = params.data.scheduled_end_at.substring(11, 16);

            return `
                <div class="h-full flex flex-col justify-center leading-tight">
                    <div class="font-medium">
                        ${date}
                    </div>

                    <div class="text-xs text-gray-500">
                        ${startTime} às ${endTime}
                    </div>
                </div>
            `;
        },
    },

    {
        headerName: 'Paciente e Aluno',
        flex: 2.5,

        cellRenderer: (params: any) => `
            <div class="h-full flex flex-col justify-center leading-tight">
                <div class="font-medium">
                    ${params.data.patient?.name ?? '-'}
                </div>

                <div class="text-xs text-gray-500">
                    ${params.data.student?.name ?? '-'}
                </div>
            </div>
        `,
    },

    {
        headerName: 'Clínica e Período',
        flex: 1,
        cellRenderer: (params: any) => `
            <div class="h-full flex flex-col justify-center leading-tight">
                <div class="font-medium">
                    ${params.data.clinic?.name ?? '-'}
                </div>

                <div class="text-xs text-gray-500">
                    ${params.data.period?.name ?? '-'}
                </div>
            </div>
        `,
    },

    {
        headerName: 'Status',
        field: 'status',
        flex: 1,
        cellRenderer: StatusBadgeAppointment,
    },
    {
        headerName: 'WhatsApp',
        flex: 0.8,
        cellRenderer: WhatsappButton,
        cellRendererParams: (params: any) => ({
            phone: params.data.patient?.phone,
            message: whatsappMessage(params.data),
        }),
    },
    {
        headerName: 'Ações',
        flex: 1,
        cellRenderer: AppointmentActions,
        cellRendererParams: () => ({
            onConfirm: confirmAppointment,
            onCancel: cancelAppointment,
            onSchedule: scheduleAppointment,
        }),
    },
];

const defaultColDef = {
    flex: 1,
    resizable: true,
};

</script>

<template>
    <div>
        <div
            class="ag-theme-alpine overflow-hidden rounded-lg border border-gray-200"
            style="height: 500px;"
        >
            <AgGridVue
                class="ag-theme-alpine h-full"
                :rowData="appointments"
                :columnDefs="columnDefs"
                :defaultColDef="defaultColDef"
                :rowHeight="60"
                :localeText="AG_GRID_LOCALE_BR"
            />
        </div>
    </div>
</template>