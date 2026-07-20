<script setup lang="ts">
import { inject } from 'vue';
import { Button } from '@/components/ui/button';
import { ClinicManagementKey, } from '@/keys/clinics-management/clinicManagementKeys';
import type { ClinicManagementRow } from '@/types/clinics-management/clinicManagement';

const props = defineProps<{
    clinic: ClinicManagementRow;
}>();

const ctx = inject(ClinicManagementKey);

function onManage() {
    ctx?.goManageClinic(props.clinic.clinic_id);
}
</script>

<template>
    <article
        class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-sky-200 hover:shadow-md"
    >
        <header class="mb-4 border-b border-gray-100 pb-3">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ clinic.clinic_name }}
            </h2>

            <p class="mt-1 text-xs text-gray-500">
                Gerencie pacientes e lista de espera
            </p>
        </header>

        <div class="flex-1 space-y-3">
            <div
                class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"
            >
                <span class="text-sm text-gray-600">
                    Pacientes ativos
                </span>

                <span
                    class="text-base font-semibold text-gray-900"
                >
                    {{ clinic.active_patients_count }}
                </span>
            </div>

            <div
                class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"
            >
                <span class="text-sm text-gray-600">
                    Lista de espera
                </span>

                <span
                    class="text-base font-semibold text-gray-900"
                >
                    {{ clinic.waiting_patients_count }}
                </span>
            </div>
        </div>

        <Button
            class="mt-4 h-9 w-full cursor-pointer bg-sky-600 text-white hover:bg-sky-700"
            @click="onManage"
        >
            Gerenciar clínica
        </Button>
    </article>
</template>