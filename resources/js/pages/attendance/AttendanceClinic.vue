<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceStudentsTable from './AttendanceStudentsTable.vue';
import { AttendanceKey } from '@/keys/attendance/attendanceKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { provide, ref } from 'vue';

const props = defineProps<{
    clinic: {
        clinic_id: number;
        clinic_name: string;
    };
    periods: {
        id: number;
        label: string;
    }[];
}>();

const loading = ref(false);

const attendance = {
    clinic: ref(props.clinic),
    periods: ref(props.periods),

    selectedPeriodId: ref<number | null>(null),
    selectedDate: ref<number | null>(null),

    dates: ref([]),
    students: ref([]),
};

provide(AttendanceKey, attendance);
provide(LoadingKey, loading);
</script>

<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <AttendanceStudentsTable />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>