<script setup lang="ts">
import { provide, ref } from 'vue';
import { Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import PatientsReportFilters from './components/PatientsReportFilters.vue';
import PatientsReportSummaryCards from './components/PatientsReportSummaryCards.vue';
import PatientsReportTable from './PatientsReportTable.vue';
import { PatientsReportKey } from '@/keys/patients-report/patientsReportKeys';
import { usePatientsReport } from '@/composables/patients-report/usePatientsReport';
import type { PatientReportFilters, } from '@/types/patients-report/patientsReport';

const props = defineProps<{
    filters: PatientReportFilters;
}>();

const patients = usePatientsReport(props.filters);

provide(PatientsReportKey, patients);

const showFilters = ref(false);
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <div
                class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
            >
                <PageHeader
                    title="Relatório de Pacientes"
                    description="Consulte, filtre e exporte os pacientes cadastrados."
                >
                    <template #actions>
                        <Button
                            type="button"
                            class="cursor-pointer gap-2 bg-sky-600 text-white hover:bg-sky-700"
                            @click="patients.exportExcel()"
                        >
                            <Download class="h-4 w-4" />
                            Exportar Excel
                        </Button>
                    </template>
                </PageHeader>
            </div>

            <div
                class="rounded-xl border border-gray-200 bg-white shadow-sm"
            >
                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center justify-between px-5 py-4 text-left"
                    @click="showFilters = !showFilters"
                >
                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M22 3H2l8 9.46V19l4 2v-8.54L22 3Z"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">
                                Filtros
                            </h2>

                            <p class="text-xs text-gray-500">
                                Refine os resultados do relatório
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-3">

                        <span
                            v-if="patients.hasActiveFilters.value"
                            class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700"
                        >
                            {{ patients.activeFiltersCount.value }}
                            {{
                                patients.activeFiltersCount.value === 1
                                    ? 'filtro'
                                    : 'filtros'
                            }}
                            aplicado{{
                                patients.activeFiltersCount.value === 1
                                    ? ''
                                    : 's'
                            }}
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="text-gray-400 transition-transform duration-200"
                            :class="{
                                'rotate-180': showFilters,
                            }"
                        >
                            <path d="m6 9 6 6 6-6" />
                        </svg>

                    </div>
                </button>

                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[600px] opacity-100"
                    leave-active-class="transition-all duration-250 ease-in"
                    leave-from-class="max-h-[600px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div
                        v-if="showFilters"
                        class="overflow-hidden border-t border-gray-100"
                    >
                        <PatientsReportFilters />
                    </div>
                </Transition>
            </div>

            <PatientsReportSummaryCards />

            <PatientsReportTable />

        </div>
    </AppLayout>
</template>