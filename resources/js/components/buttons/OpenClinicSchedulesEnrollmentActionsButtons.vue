<script setup lang="ts">
import type { OpenClinicScheduleEnrollmentRow } from '@/types/schedule-enrollment/openClinicSchedulesEnrollment';
import { Check, Lock, BadgeCheck } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data?: OpenClinicScheduleEnrollmentRow;
        onEnroll?: (row: OpenClinicScheduleEnrollmentRow) => void;
    };
}>();

function enroll() {
    if (!props.params.data) return;

    props.params.onEnroll?.(props.params.data);
}

</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">

        <BadgeCheck
            v-if="params.data?.is_enrolled"
            class="text-green-500"
            :size="18"
            title="Você já está inscrito"
        />

        <Lock
            v-else-if="!params.data?.allow_student_booking"
            class="text-gray-400"
            :size="18"
            title="Inscrições desabilitadas"
        />

        <Check
            v-else
            class="cursor-pointer text-sky-500 hover:text-sky-700"
            :size="18"
            title="Inscrever-se"
            @click="enroll"
        />
    </div>
</template>