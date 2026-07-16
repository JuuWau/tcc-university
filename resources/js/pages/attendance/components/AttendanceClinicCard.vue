<script setup lang="ts">
import { inject } from 'vue';
import { Button } from '@/components/ui/button';
import { AttendanceManagementKey } from '@/keys/attendance/attendanceManagementKeys';
import type { AttendanceClinic } from '@/types/attendance/attendance';

const props = defineProps<{
    clinic: AttendanceClinic;
}>();

const ctx = inject(AttendanceManagementKey);

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
                Gerencie o comparecimento dos alunos desta clínica.
            </p>
        </header>

        <Button
            class="mt-2 h-9 w-full cursor-pointer bg-sky-600 text-white hover:bg-sky-700"
            @click="onManage"
        >
            Gerenciar comparecimento
        </Button>
    </article>
</template>
