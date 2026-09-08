<script setup lang="ts">
import { inject } from 'vue';
import { ClinicsReportKey } from '@/keys/clinics-report/clinicsReportKeys';

const clinics = inject(ClinicsReportKey);

if (!clinics) {
    throw new Error(
        'ClinicsReportKey não foi fornecido.',
    );
}

function selectStatus(status: string | null) {
    if (clinics.filters.value.status === status) {
        clinics.filters.value.status = null;
    } else {
        clinics.filters.value.status = status;
    }

    clinics.search();
}

function isStatusSelected(status: string | null) {
    return clinics.filters.value.status === status;
}
</script>

<template>
    <div
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5"
    >
        <div
            class="cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200"
            :class="
                isStatusSelected(null)
                    ? 'border-blue-500 ring-2 ring-blue-200'
                    : 'border-slate-200 hover:border-blue-300 hover:shadow-md'
            "
            @click="selectStatus(null)"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Total
                </span>

                <div class="rounded-lg bg-blue-100 p-2">
                    <i class="pi pi-building text-blue-600" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-blue-600">
                {{ clinics.summary.value.total }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Clínicas cadastradas
            </p>
        </div>

        <div
            class="cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200"
            :class="
                isStatusSelected('active')
                    ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
                    : 'border-slate-200 hover:border-green-300 hover:shadow-md'
            "
            @click="selectStatus('active')"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Ativas
                </span>

                <div class="rounded-lg bg-green-100 p-2">
                    <i class="pi pi-check-circle text-green-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-green-700">
                {{ clinics.summary.value.active }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Clínicas em funcionamento
            </p>
        </div>

        <div
            class="cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200"
            :class="
                isStatusSelected('inactive')
                    ? 'border-slate-500 bg-slate-50 ring-2 ring-slate-200'
                    : 'border-slate-200 hover:border-slate-300 hover:shadow-md'
            "
            @click="selectStatus('inactive')"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Inativas
                </span>

                <div class="rounded-lg bg-slate-200 p-2">
                    <i class="pi pi-ban text-slate-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-slate-700">
                {{ clinics.summary.value.inactive }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Clínicas desativadas
            </p>
        </div>

        <div
            class="rounded-xl border border-sky-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-sky-300 hover:shadow-md"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Com agenda
                </span>

                <div class="rounded-lg bg-sky-100 p-2">
                    <i class="pi pi-calendar text-sky-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-sky-700">
                {{ clinics.summary.value.with_schedule }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Possuem horários cadastrados
            </p>
        </div>

        <div
            class="rounded-xl border border-amber-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-amber-300 hover:shadow-md"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Sem agenda
                </span>

                <div class="rounded-lg bg-amber-100 p-2">
                    <i class="pi pi-calendar-times text-amber-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-amber-700">
                {{ clinics.summary.value.without_schedule }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Sem horários cadastrados
            </p>
        </div>
    </div>
</template>