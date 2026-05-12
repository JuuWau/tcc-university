<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    OpenClinicsManagementKey,
    type OpenClinicRow,
} from '@/keys/schedules/openClinicsManagementKeys';
import { inject } from 'vue';

const props = defineProps<{
    clinic: OpenClinicRow;
}>();

const ctx = inject(OpenClinicsManagementKey);

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
                Agenda aberta para atendimento
            </p>
        </header>

        <Button
            class="mt-2 h-9 w-full bg-sky-600 text-white hover:bg-sky-700 cursor-pointer"
            @click="onManage"
        >
            Gerenciar clínica
        </Button>
    </article>
</template>
