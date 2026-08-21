```vue
<script setup lang="ts">
import StatusBadgeAppointment from '@/components/badges/StatusBadgeAppointment.vue';
import type { PatientSchedule } from '@/types/patient/patientSchedule';

defineProps<{
    appointment: PatientSchedule;
}>();

</script>

<template>
    <div
        class="rounded-xl border border-gray-200 bg-white p-4 transition hover:border-gray-300 hover:shadow-sm"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ appointment.date }}
                        </p>

                        <p class="text-sm text-gray-500">
                            {{ appointment.start_time }}
                            -
                            {{ appointment.end_time }}
                        </p>
                    </div>

                    <StatusBadgeAppointment
                        :params="{ value: appointment.status }"
                        />
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium text-gray-400">
                            Clínica
                        </p>

                        <p class="mt-1 text-sm text-gray-700">
                            {{ appointment.clinic?.name ?? 'Não informado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-400">
                            Período
                        </p>

                        <p class="mt-1 text-sm text-gray-700">
                            {{ appointment.period?.name ?? 'Não informado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-400">
                            Aluno
                        </p>

                        <p class="mt-1 text-sm text-gray-700">
                            {{ appointment.student?.name ?? 'Não informado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-400">
                            Procedimento
                        </p>

                        <p class="mt-1 text-sm text-gray-700">
                            {{ appointment.procedure?.name ?? 'Não informado' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs font-medium text-gray-400">
                            Responsáveis
                        </p>

                        <p class="mt-1 text-sm text-gray-700">
                            <template v-if="appointment.responsibles?.length">
                                {{
                                    appointment.responsibles
                                        .map((responsible) => responsible.name)
                                        .join(', ')
                                }}
                            </template>

                            <template v-else>
                                Não informado
                            </template>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
```
