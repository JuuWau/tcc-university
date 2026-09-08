<script setup lang="ts">
import { inject } from 'vue';

import { Search, X } from 'lucide-vue-next';

import AppMultiselect from '@/components/AppMultiselect.vue';
import Button from '@/components/ui/button/Button.vue';

import { ClinicsReportKey } from '@/keys/clinics-report/clinicsReportKeys';

const clinics = inject(ClinicsReportKey);

if (!clinics) {
    throw new Error(
        'ClinicsReportKey não foi fornecido.',
    );
}
</script>

<template>
    <div class="space-y-5 p-6">
        <div>
            <label
                class="mb-2 block text-sm font-medium text-gray-700"
            >
                Pesquisar
            </label>

            <div class="relative">
                <Search
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />

                <input
                    v-model="clinics.filters.value.search"
                    type="text"
                    placeholder="Pesquisar por nome da clínica..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    @input="clinics.search()"
                />
            </div>
        </div>

        <div
            class="grid grid-cols-1 gap-4 md:grid-cols-2"
        >
            <div>
                <label
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Período
                </label>

                <AppMultiselect
                    v-model="clinics.filters.value.period_id"
                    :options="clinics.periods.value"
                    label="name"
                    value-prop="id"
                    placeholder="Todos os períodos"
                    :searchable="true"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="clinics.search()"
                />
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Status
                </label>

                <AppMultiselect
                    v-model="clinics.filters.value.status"
                    :options="[
                        {
                            value: 'active',
                            label: 'Ativa',
                        },
                        {
                            value: 'inactive',
                            label: 'Inativa',
                        },
                    ]"
                    label="label"
                    value-prop="value"
                    placeholder="Todos os status"
                    :searchable="false"
                    :can-clear="true"
                    :append-to-body="true"
                    @update:modelValue="clinics.search()"
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
                @click="clinics.clearFilters()"
            >
                <X class="h-4 w-4" />
                Limpar filtros
            </Button>
        </div>
    </div>
</template>