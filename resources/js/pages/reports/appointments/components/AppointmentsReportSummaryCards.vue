<script setup lang="ts">
import { inject } from 'vue';
import { AppointmentsKey } from '@/keys/appointments-report/appointmentsKeys';

const appointments = inject(AppointmentsKey);

function selectStatus(status: string | null) {
    if (appointments.filters.value.status === status) {
        appointments.filters.value.status = null;
    } else {
        appointments.filters.value.status = status;
    }

    appointments.search();
}

function isSelected(status: string | null) {
    return appointments.filters.value.status === status;
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-7">

        <div
            @click="selectStatus(null)"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected(null)
                    ? 'border-blue-500 ring-2 ring-blue-200'
                    : 'border-slate-200 hover:border-blue-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Total
                </span>

                <div class="rounded-lg bg-blue-100 p-2">
                    <i class="pi pi-calendar text-blue-600" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-blue-600">
                {{ appointments.summary.value.total }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Agendamentos
            </p>
        </div>

        <div
            @click="selectStatus('scheduled')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('scheduled')
                    ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-200'
                    : 'border-slate-200 hover:border-sky-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Agendados
                </span>

                <div class="rounded-lg bg-sky-200 p-2">
                    <i class="pi pi-calendar text-sky-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-sky-900">
                {{ appointments.summary.value.scheduled }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Aguardando atendimento
            </p>
        </div>

        <div
            @click="selectStatus('confirmed')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('confirmed')
                    ? 'border-lime-500 bg-lime-50 ring-2 ring-lime-200'
                    : 'border-slate-200 hover:border-lime-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Confirmados
                </span>

                <div class="rounded-lg bg-lime-300 p-2">
                    <i class="pi pi-check-circle text-lime-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-lime-900">
                {{ appointments.summary.value.confirmed }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Confirmados
            </p>
        </div>

        <div
            @click="selectStatus('completed')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('completed')
                    ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200'
                    : 'border-slate-200 hover:border-teal-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Concluídos
                </span>

                <div class="rounded-lg bg-teal-300 p-2">
                    <i class="pi pi-check text-teal-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-teal-900">
                {{ appointments.summary.value.completed }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Atendimentos finalizados
            </p>
        </div>

        <div
            @click="selectStatus('canceled')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('canceled')
                    ? 'border-rose-500 bg-rose-50 ring-2 ring-rose-200'
                    : 'border-slate-200 hover:border-rose-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Cancelados
                </span>

                <div class="rounded-lg bg-slate-300 p-2">
                    <i class="pi pi-times-circle text-slate-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-slate-900">
                {{ appointments.summary.value.canceled }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Agendamentos cancelados
            </p>
        </div>

        <div
            @click="selectStatus('no_show')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('no_show')
                    ? 'border-red-500 bg-red-50 ring-2 ring-red-200'
                    : 'border-slate-200 hover:border-red-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Não compareceu
                </span>

                <div class="rounded-lg bg-red-300 p-2">
                    <i class="pi pi-user-minus text-red-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-red-900">
                {{ appointments.summary.value.no_show }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Pacientes que não compareceram
            </p>
        </div>

        <div
            @click="selectStatus('rescheduled')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('rescheduled')
                    ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-200'
                    : 'border-slate-200 hover:border-amber-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Remarcados
                </span>

                <div class="rounded-lg bg-amber-400 p-2">
                    <i class="pi pi-refresh text-amber-950" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-amber-950">
                {{ appointments.summary.value.rescheduled }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Reagendamentos
            </p>
        </div>

    </div>
</template>