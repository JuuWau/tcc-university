<script setup lang="ts">
import { inject } from 'vue';

import { PatientsReportKey } from '@/keys/patients-report/patientsReportKeys';

const patients = inject(PatientsReportKey);

function selectStatus(status: string | null) {
    if (patients.filters.value.status === status) {
        patients.filters.value.status = null;
    } else {
        patients.filters.value.status = status;
    }

    patients.search();
}

function isSelected(status: string | null) {
    return patients.filters.value.status === status;
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
                    <i class="pi pi-users text-blue-600" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-blue-600">
                {{ patients.summary.value.total }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Pacientes
            </p>
        </div>

        <div
            @click="selectStatus('ativo')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('ativo')
                    ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
                    : 'border-slate-200 hover:border-green-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Ativos
                </span>

                <div class="rounded-lg bg-green-200 p-2">
                    <i class="pi pi-user text-green-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-green-900">
                {{ patients.summary.value.ativo }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Pacientes ativos
            </p>
        </div>

        <div
            @click="selectStatus('tratamento')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('tratamento')
                    ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-200'
                    : 'border-slate-200 hover:border-sky-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Em tratamento
                </span>

                <div class="rounded-lg bg-sky-200 p-2">
                    <i class="pi pi-heart text-sky-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-sky-900">
                {{ patients.summary.value.tratamento }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Em acompanhamento
            </p>
        </div>

        <div
            @click="selectStatus('pausa_tratamento')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('pausa_tratamento')
                    ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-200'
                    : 'border-slate-200 hover:border-amber-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Em pausa
                </span>

                <div class="rounded-lg bg-amber-200 p-2">
                    <i class="pi pi-pause text-amber-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-amber-900">
                {{ patients.summary.value.pausa_tratamento }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Tratamento pausado
            </p>
        </div>

        <div
            @click="selectStatus('inativo')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('inativo')
                    ? 'border-slate-500 bg-slate-50 ring-2 ring-slate-200'
                    : 'border-slate-200 hover:border-slate-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Inativos
                </span>

                <div class="rounded-lg bg-slate-300 p-2">
                    <i class="pi pi-user-minus text-slate-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-slate-900">
                {{ patients.summary.value.inativo }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Pacientes inativos
            </p>
        </div>

        <div
            @click="selectStatus('abandono')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('abandono')
                    ? 'border-red-500 bg-red-50 ring-2 ring-red-200'
                    : 'border-slate-200 hover:border-red-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Abandono
                </span>

                <div class="rounded-lg bg-red-200 p-2">
                    <i class="pi pi-exclamation-triangle text-red-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-red-900">
                {{ patients.summary.value.abandono }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Tratamentos abandonados
            </p>
        </div>

        <div
            @click="selectStatus('concluido')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('concluido')
                    ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200'
                    : 'border-slate-200 hover:border-teal-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Concluídos
                </span>

                <div class="rounded-lg bg-teal-200 p-2">
                    <i class="pi pi-check-circle text-teal-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-teal-900">
                {{ patients.summary.value.concluido }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Tratamentos concluídos
            </p>
        </div>

        <div
            @click="selectStatus('transferencia')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isSelected('transferencia')
                    ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200'
                    : 'border-slate-200 hover:border-purple-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Transferências
                </span>

                <div class="rounded-lg bg-purple-200 p-2">
                    <i class="pi pi-arrow-right text-purple-900" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-purple-900">
                {{ patients.summary.value.transferencia }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Pacientes transferidos
            </p>
        </div>
    </div>
</template>