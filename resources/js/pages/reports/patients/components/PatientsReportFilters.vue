<script setup lang="ts">
import { inject } from 'vue';
import { Search, X } from 'lucide-vue-next';

import { PatientsReportKey } from '@/keys/patients-report/patientsReportKeys';
import AppMultiselect from '@/components/AppMultiselect.vue';
import Button from '@/components/ui/button/Button.vue';

const patients = inject(PatientsReportKey);

if (!patients) {
    throw new Error('PatientsReportKey não foi fornecido.');
}
</script>
<template>
    <div class="space-y-6 p-6">
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Pesquisar
            </label>

            <div class="relative">
                <Search
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />

                <input
                    v-model="patients.filters.value.search"
                    type="text"
                    placeholder="Pesquisar por nome, código, CPF ou telefone..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    @input="patients.search()"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Tipo
                </label>

                <AppMultiselect
                    v-model="patients.filters.value.patient_type"
                    :options="patients.filters.value.patient_types"
                    label="name"
                    value-prop="id"
                    placeholder="Todos os tipos"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="patients.search"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Status
                </label>

                <AppMultiselect
                    v-model="patients.filters.value.status"
                    :options="patients.filters.value.statuses"
                    label="name"
                    value-prop="id"
                    placeholder="Todos os status"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="patients.search"
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
                @click="patients.clearFilters()"
            >
                <X class="h-4 w-4" />

                Limpar filtros
            </Button>
        </div>
    </div>
</template>