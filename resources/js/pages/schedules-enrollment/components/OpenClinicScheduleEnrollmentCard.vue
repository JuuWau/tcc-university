<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    OpenClinicsManagementScheduleKey,
    type OpenClinicScheduleRow,
} from '@/keys/schedule-enrollment/openClinicsScheduleManagementKeys';
import { inject } from 'vue';

const props = defineProps<{
    clinic: OpenClinicScheduleRow;
}>();

const labelMap = {
    fully_enrolled: 'Inscrito',
    partially_enrolled: 'Inscrito parcialmente',
    not_enrolled: 'Não inscrito',
};

const colorMap = {
    fully_enrolled: 'bg-green-100 text-green-700',
    partially_enrolled: 'bg-yellow-100 text-yellow-700',
    not_enrolled: 'bg-red-100 text-red-600',
};

const ctx = inject(OpenClinicsManagementScheduleKey);

function onManage() {
    ctx?.goManageClinicSchedule(props.clinic.clinic_id);
}
</script>

<template>
    <article
        class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-sky-200 hover:shadow-md"
    >
        <header class="mb-4 border-b border-gray-100 pb-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ clinic.clinic_name }}
                </h2>

                <span
                    class="rounded-full px-2 py-1 text-xs font-medium"
                    :class="colorMap[clinic.enrollment_status]"
                >
                    {{ labelMap[clinic.enrollment_status] }}
                </span>
            </div>

            <p class="mt-1 text-xs text-gray-500">
                Agenda aberta para atendimento
            </p>
        </header>

        <Button
            class="mt-2 h-9 w-full bg-sky-600 text-white hover:bg-sky-700"
            @click="onManage"
        >
            Gerenciar
        </Button>
    </article>
</template>
