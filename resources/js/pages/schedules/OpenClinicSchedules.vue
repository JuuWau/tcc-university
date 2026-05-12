<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <ScheduleSlotEditModal />
                        <ScheduleSlotDeleteModal />
                        <ScheduleSlotCreateModal />
                        <ScheduleSlotDeleteMultipleModal />
                        <ScheduleSlotsEditModal />
                        <ScheduleSlotAddStudentsModal />

                        <OpenClinicSchedulesTable
                            @edit="onEditSlot"
                            @remove="onRemoveSlot"
                            @removeMultiple="onRemoveMultipleSlots"
                            @create="onRegisterNewDay"
                            @editMultiple="onEditMultipleSlots"
                            @addStudents="onAddStudentsToSlots"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    ScheduleSlotCreateKey,
    ScheduleSlotDeleteKey,
    ScheduleSlotDeleteMultipleKey,
    ScheduleSlotEditMultipleKey,
    ScheduleSlotEditKey,
    ScheduleSlotAddStudentsKey
} from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import ScheduleSlotCreateModal from '@/pages/schedules/components/ScheduleSlotCreateModal.vue';
import ScheduleSlotDeleteModal from '@/pages/schedules/components/ScheduleSlotDeleteModal.vue';
import ScheduleSlotEditModal from '@/pages/schedules/components/ScheduleSlotEditModal.vue';
import ScheduleSlotsEditModal from '@/pages/schedules/components/ScheduleSlotsEditModal.vue';
import ScheduleSlotDeleteMultipleModal from '@/pages/schedules/components/ScheduleSlotsDeleteModal.vue';
import OpenClinicSchedulesTable from '@/pages/schedules/OpenClinicSchedulesTable.vue';
import ScheduleSlotAddStudentsModal from '@/pages/schedules/components/ScheduleSlotAddStudentsModal.vue';
import type { OpenClinicScheduleRow } from '@/types/schedule/openClinicSchedules';
import { provide, ref } from 'vue';

const loading = ref(false);

const createModal = {
    isOpen: ref(false),
    clinicId: ref<number | null>(null),
    periodId: ref<number | null>(null),
};

const editModal = {
    isOpen: ref(false),
    row: ref<OpenClinicScheduleRow | null>(null),
};

const deleteModal = {
    isOpen: ref(false),
    row: ref<OpenClinicScheduleRow | null>(null),
};

const deleteMultipleModal = {
    isOpen: ref(false),
    slots: ref<OpenClinicScheduleRow[]>([]),
};

const editMultipleModal = {
    isOpen: ref(false),
    slots: ref<OpenClinicScheduleRow[]>([]),
};

const addStudentsModal = {
    isOpen: ref(false),
    slots: ref<OpenClinicScheduleRow[]>([]),
};

provide(ScheduleSlotEditKey, editModal);
provide(ScheduleSlotDeleteKey, deleteModal);
provide(ScheduleSlotCreateKey, createModal);
provide(ScheduleSlotDeleteMultipleKey, deleteMultipleModal);
provide(ScheduleSlotEditMultipleKey, editMultipleModal);
provide(ScheduleSlotAddStudentsKey, addStudentsModal);
provide(LoadingKey, loading);

function onEditSlot(row: OpenClinicScheduleRow) {
    editModal.row.value = row;
    editModal.isOpen.value = true;
}

function onRegisterNewDay(clinicId: number, periodId: number | null) {
    createModal.clinicId.value = clinicId;
    createModal.periodId.value = periodId;
    createModal.isOpen.value = true;
}

function onRemoveSlot(row: OpenClinicScheduleRow) {
    deleteModal.row.value = row;
    deleteModal.isOpen.value = true;
}

function onRemoveMultipleSlots(slots: OpenClinicScheduleRow[]) {
    deleteMultipleModal.slots.value = slots;
    deleteMultipleModal.isOpen.value = true;
}

function onEditMultipleSlots(slots: OpenClinicScheduleRow[]) {
    editMultipleModal.slots.value = slots;
    editMultipleModal.isOpen.value = true;
}

function onAddStudentsToSlots(slots: OpenClinicScheduleRow[]) {
    addStudentsModal.slots.value = slots;
    addStudentsModal.isOpen.value = true;
}
</script>
