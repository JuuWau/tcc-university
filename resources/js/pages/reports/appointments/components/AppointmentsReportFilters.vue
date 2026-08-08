<script setup lang="ts">
import { inject } from 'vue';

import { X, Search } from 'lucide-vue-next';

import AppMultiselect from '@/components/AppMultiselect.vue';
import Button from '@/components/ui/button/Button.vue';

import { AppointmentsKey } from '@/keys/appointments-report/appointmentsKeys';

const appointments = inject(AppointmentsKey);

if (!appointments) {
    throw new Error(
        'AppointmentsReportFilters: AppointmentsKey não foi encontrado.'
    );
}
</script>

<template>
    <div class="space-y-5 p-5">

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Pesquisar
            </label>

            <div class="relative">
                <Search
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />

                <input
                    v-model="appointments.filters.value.search"
                    type="text"
                    placeholder="Pesquisar paciente ou aluno..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    @input="appointments.search()"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Clínica
                </label>

                <AppMultiselect
                    v-model="appointments.filters.value.clinic_id"
                    :options="appointments.clinics.value"
                    label="name"
                    value-prop="id"
                    placeholder="Todas as clínicas"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="appointments.search"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Responsável
                </label>

                <AppMultiselect
                    v-model="appointments.filters.value.responsible_id"
                    :options="appointments.responsibles.value"
                    label="name"
                    value-prop="id"
                    placeholder="Todos os responsáveis"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="appointments.search"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Período
                </label>

                <AppMultiselect
                    v-model="appointments.filters.value.period_id"
                    :options="appointments.periods.value"
                    label="name"
                    value-prop="id"
                    placeholder="Todos os períodos"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="appointments.search"
                />
            </div>

        </div>

        <div class="flex items-center gap-3">
            <div class="h-px flex-1 bg-gray-100"></div>
            <span
                class="text-xs font-medium uppercase tracking-wide text-gray-400"
            >
                Período do relatório
            </span>
            <div class="h-px flex-1 bg-gray-100"></div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Data inicial
                </label>

                <input
                    v-model="appointments.filters.value.start_date"
                    type="date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    @input="appointments.search()"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Data final
                </label>

                <input
                    v-model="appointments.filters.value.end_date"
                    type="date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    @input="appointments.search()"
                />
            </div>

        </div>

        <div
            class="flex flex-col gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end"
        >
            <Button
                variant="outline"
                type="button"
                class="cursor-pointer gap-2"
                @click="appointments.clearFilters()"
            >
                <X class="h-4 w-4" />

                Limpar filtros
            </Button>
        </div>
    </div>
</template>