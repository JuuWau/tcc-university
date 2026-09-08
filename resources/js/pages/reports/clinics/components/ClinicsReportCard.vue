<script setup lang="ts">
import type { ClinicReport } from '@/types/clinics-report/clinicsReport';

defineProps<{
    clinic: ClinicReport;
}>();
</script>

<template>
    <div
        class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3
                    class="truncate text-sm font-semibold text-gray-900"
                >
                    {{ clinic.name }}
                </h3>

                <p class="mt-1 text-xs text-gray-500">
                    Cadastro:
                    {{
                        clinic.created_at
                            ? new Date(
                                  clinic.created_at,
                              ).toLocaleDateString(
                                  'pt-BR',
                              )
                            : '—'
                    }}
                </p>
            </div>

            <span
                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium"
                :class="
                    clinic.active
                        ? 'bg-green-50 text-green-700'
                        : 'bg-slate-100 text-slate-600'
                "
            >
                {{ clinic.active ? 'Ativa' : 'Inativa' }}
            </span>
        </div>

        <div
            class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4"
        >
            <div>
                <p class="text-xs text-gray-500">
                    Horários
                </p>

                <p class="mt-1 text-lg font-semibold text-gray-900">
                    {{ clinic.schedule_slots_count }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">
                    Vagas
                </p>

                <p class="mt-1 text-lg font-semibold text-gray-900">
                    {{ clinic.available_slots_sum ?? 0 }}
                </p>
            </div>
        </div>
    </div>
</template>