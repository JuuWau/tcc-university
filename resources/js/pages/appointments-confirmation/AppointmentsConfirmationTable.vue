<script setup lang="ts">
import { computed, inject } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { AG_GRID_LOCALE_BR } from '@ag-grid-community/locale';
import StatusBadgeAppointment from '@/components/badges/StatusBadgeAppointment.vue';
import WhatsappButton from '@/components/buttons/WhatsappButton.vue';
import AppointmentActions from '@/components/buttons/AppointmentActions.vue';
import AppointmentConfirmationCard from './components/AppointmentConfirmationCard.vue';
import { AppointmentsConfirmationKey } from '@/keys/appointments-confirmation/appointmentsConfirmationKeys';

import {
    formatDateBr,
    formatDateTimeBr,
} from '@/src/utils/formatters';

const context = inject(AppointmentsConfirmationKey);

if (!context) {
    throw new Error(
        'AppointmentsConfirmation deve ser usado dentro de AppointmentsConfirmation.',
    );
}

const appointments = computed(
    () => context.appointments.value,
);

const loading = computed(
    () => context.loading.value,
);

async function confirmAppointment(row: any) {
    await context.updateAppointmentStatus(
        row.id,
        'confirmed',
    );
}

async function cancelAppointment(row: any) {
    await context.updateAppointmentStatus(
        row.id,
        'canceled',
    );
}

async function scheduleAppointment(row: any) {
    await context.updateAppointmentStatus(
        row.id,
        'scheduled',
    );
}

function whatsappMessage(appointment: any) {
    const patientName =
        appointment.patient?.name ?? 'Paciente';

    const date = formatDateBr(
        appointment.scheduled_start_at,
    );

    const startTime =
        appointment.scheduled_start_at.substring(11, 16);

    return `Olá ${patientName},

Seu atendimento está agendado para ${date} às ${startTime}.

Podemos confirmar sua presença?`;
}

function openWhatsapp(appointment: any) {
    const phone = appointment.patient?.phone;

    if (!phone) {
        return;
    }

    const message = whatsappMessage(appointment);

    const whatsappUrl =
        `https://wa.me/${phone.replace(/\D/g, '')}?text=${encodeURIComponent(message)}`;

    window.open(
        whatsappUrl,
        '_blank',
    );
}

const columnDefs = [
    {
        headerName: 'Data e Horário',
        flex: 0.8,

        cellRenderer: (params: any) => {
            if (!params.data) {
                return '';
            }

            const date = formatDateTimeBr(
                params.data.scheduled_start_at,
            );

            const startTime =
                params.data.scheduled_start_at.substring(11, 16);

            const endTime =
                params.data.scheduled_end_at.substring(11, 16);

            return `
                <div class="flex h-full flex-col justify-center leading-tight">
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
            <div class="flex h-full flex-col justify-center leading-tight">
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
        flex: 1.2,

        cellRenderer: (params: any) => `
            <div class="flex h-full flex-col justify-center leading-tight">
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
    <div class="w-full">
        <div class="hidden md:block">
            <div
                class="ag-theme-alpine h-[500px] overflow-hidden rounded-lg border border-gray-200"
            >
                <AgGridVue
                    class="ag-theme-alpine h-full w-full"
                    :rowData="appointments"
                    :columnDefs="columnDefs"
                    :defaultColDef="defaultColDef"
                    :rowHeight="60"
                    :localeText="AG_GRID_LOCALE_BR"
                />
            </div>
        </div>

        <div class="space-y-3 md:hidden">
            <div
                v-if="loading"
                class="flex min-h-40 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm text-gray-500"
            >
                Carregando agendamentos...
            </div>

            <template v-else>
                <div
                    v-if="appointments.length === 0"
                    class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-10 text-center"
                >
                    <p class="text-sm font-medium text-gray-600">
                        Nenhum agendamento encontrado.
                    </p>

                    <p class="mt-1 text-xs text-gray-400">
                        Não existem agendamentos para os filtros selecionados.
                    </p>
                </div>

                <AppointmentConfirmationCard
                    v-for="appointment in appointments"
                    :key="appointment.id"
                    :appointment="appointment"
                    @confirm="confirmAppointment"
                    @cancel="cancelAppointment"
                    @schedule="scheduleAppointment"
                    @whatsapp="openWhatsapp"
                />
            </template>
        </div>
    </div>
</template>
