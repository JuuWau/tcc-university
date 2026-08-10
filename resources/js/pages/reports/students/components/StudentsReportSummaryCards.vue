<script setup lang="ts">
import { inject } from 'vue';

import { StudentsReportKey } from '@/keys/students-report/studentsReportKeys';

const students = inject(StudentsReportKey);

if (!students) {
    throw new Error(
        'StudentsReportKey não foi fornecido.',
    );
}

function selectStatus(status: string | null) {
    if (students.filters.value.status === status) {
        students.filters.value.status = null;
    } else {
        students.filters.value.status = status;
    }

    students.search();
}

function selectInvitation(status: string | null) {
    if (
        students.filters.value.invitation_status === status
    ) {
        students.filters.value.invitation_status = null;
    } else {
        students.filters.value.invitation_status = status;
    }

    students.search();
}

function isStatusSelected(status: string | null) {
    return students.filters.value.status === status;
}

function isInvitationSelected(status: string | null) {
    return students.filters.value.invitation_status === status;
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div
            @click="selectStatus(null)"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isStatusSelected(null)
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
                {{ students.summary.value.total }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Estudantes cadastrados
            </p>
        </div>

        <div
            @click="selectStatus('active')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isStatusSelected('active')
                    ? 'border-green-500 bg-green-50 ring-2 ring-green-200'
                    : 'border-slate-200 hover:border-green-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Ativos
                </span>

                <div class="rounded-lg bg-green-100 p-2">
                    <i class="pi pi-user text-green-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-green-700">
                {{ students.summary.value.active }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Estudantes com vínculo ativo
            </p>
        </div>

        <div
            @click="selectStatus('inactive')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isStatusSelected('inactive')
                    ? 'border-slate-500 bg-slate-50 ring-2 ring-slate-200'
                    : 'border-slate-200 hover:border-slate-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Inativos
                </span>

                <div class="rounded-lg bg-slate-200 p-2">
                    <i class="pi pi-user-minus text-slate-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-slate-700">
                {{ students.summary.value.inactive }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Estudantes inativos
            </p>
        </div>

        <div
            @click="selectInvitation('accepted')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isInvitationSelected('accepted')
                    ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200'
                    : 'border-slate-200 hover:border-teal-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Convites aceitos
                </span>

                <div class="rounded-lg bg-teal-100 p-2">
                    <i class="pi pi-check-circle text-teal-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-teal-700">
                {{ students.summary.value.invitation_accepted }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Estudantes que aceitaram o convite
            </p>
        </div>

        <div
            @click="selectInvitation('pending')"
            :class="[
                'cursor-pointer rounded-xl border bg-white p-5 shadow-sm transition-all duration-200',
                isInvitationSelected('pending')
                    ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-200'
                    : 'border-slate-200 hover:border-amber-300 hover:shadow-md',
            ]"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">
                    Convites pendentes
                </span>

                <div class="rounded-lg bg-amber-100 p-2">
                    <i class="pi pi-clock text-amber-700" />
                </div>
            </div>

            <h2 class="mt-4 text-3xl font-bold text-amber-700">
                {{ students.summary.value.invitation_pending }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Aguardando resposta
            </p>
        </div>
    </div>
</template>