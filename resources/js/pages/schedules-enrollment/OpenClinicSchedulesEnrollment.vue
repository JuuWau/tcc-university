<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <SchedulesSlotsEnrollmentsModal />
                        <SchedulesSlotEnrollmentModal />

                        <OpenClinicScheduleEnrollmentTable
                            @enroll="onEnrollSlot"
                            @enroll-multiple="onEnrollMultipleSlots"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    ScheduleSlotEnrollmentKey,
    ScheduleSlotEnrollmentMultipleKey,
} from '@/keys/schedule-enrollment/scheduleSlotEnrollmentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import SchedulesSlotsEnrollmentsModal from '@/pages/schedules-enrollment/components/SchedulesSlotsEnrollmentsModal.vue';
import SchedulesSlotEnrollmentModal from '@/pages/schedules-enrollment/components/SchedulesSlotEnrollmentModal.vue';
import OpenClinicScheduleEnrollmentTable from '@/pages/schedules-enrollment/OpenClinicScheduleEnrollmentTable.vue';
import type { OpenClinicScheduleEnrollmentRow } from '@/types/schedule-enrollment/openClinicSchedulesEnrollment';
import { provide, ref } from 'vue';

const loading = ref(false);

const enrollmentModal = {
    isOpen: ref(false),
    slot: ref<OpenClinicScheduleEnrollmentRow | null>(null),
};

const enrollmentMultipleModal = {
    isOpen: ref(false),
    slots: ref<OpenClinicScheduleEnrollmentRow[]>([]),
};

provide(ScheduleSlotEnrollmentKey, enrollmentModal);
provide(ScheduleSlotEnrollmentMultipleKey, enrollmentMultipleModal);
provide(LoadingKey, loading);

function onEnrollSlot(row: OpenClinicScheduleEnrollmentRow) {
    enrollmentModal.slot.value = row;
    enrollmentModal.isOpen.value = true;
}

function onEnrollMultipleSlots(slots: OpenClinicScheduleEnrollmentRow[]) {
    enrollmentMultipleModal.slots.value = slots;
    enrollmentMultipleModal.isOpen.value = true;
}
</script>
