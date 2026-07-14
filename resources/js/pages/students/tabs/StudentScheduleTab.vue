<script setup lang="ts">
import { inject, onMounted, provide, ref } from 'vue';
import { useStudentSchedule } from '@/composables/students/useStudentSchedule';
import { StudentScheduleContextKey } from '@/keys/students/studentScheduleKeys';
import StudentScheduleToolbar from './components/StudentScheduleToolbar.vue';
import StudentScheduleCalendar from './components/StudentScheduleCalendar.vue';
import StudentScheduleDayEvents from './components/StudentScheduleDayEvents.vue';
import { StudentTabContext, StudentTabContextKey } from '@/keys/students/studentKeys.js';
import { AppointmentCreateModalKey, AppointmentDetailsModalKey } from '@/keys/appointment/useAppointmentKeys.js';
import StudentAppointmentEditModal from './components/StudentAppointmentEditModal.vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { edit } from '@/routes/appearance/index.js';
import StudentAppointmentCreateModal from './components/StudentAppointmentCreateModal.vue';

const ctx = inject(StudentTabContextKey) as StudentTabContext;
const loading = ref(false);
const studentId = ctx.student.value.id;
const schedule = useStudentSchedule(studentId);
const editModal = {
    isOpen: ref(false),
    appointment: ref(null),
    patientOptions: schedule.patientOptions,
    procedureOptions: schedule.procedureOptions,
    updateAppointment: schedule.updateAppointment,
};

const createModal = {
    isOpen: ref(false),
    initialData: ref({
        date: '',
        start_time: '',
        end_time: '',
    }),
    patientOptions: schedule.patientOptions,
    procedureOptions: schedule.procedureOptions,
};

provide(StudentScheduleContextKey, schedule);
provide(AppointmentDetailsModalKey, editModal);
provide(AppointmentCreateModalKey, createModal);
provide(LoadingKey, loading);

function openEditModal(appointment: any) {
    editModal.appointment.value = appointment;
    editModal.isOpen.value = true;
}

function openCreateModal(data: {
    date: string;
    start_time: string;
    end_time: string;
}) {
    createModal.initialData.value = data;

    createModal.isOpen.value = true;
}
</script>

<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    >
        <div
            class="mb-6 border-b border-gray-200 pb-4"
        >
            <h2 class="text-lg font-semibold text-gray-900">
                Agenda
            </h2>

            <p class="text-sm text-gray-500">
                Agendamentos por clínica do estudante
            </p>
        </div>

        <div class="space-y-6">
            <StudentScheduleToolbar />

            <StudentScheduleCalendar />

            <StudentScheduleDayEvents
                :key="schedule.calendarKey.value"
                @create="openCreateModal"
                @edit="openEditModal"
            />

            <StudentAppointmentEditModal />

            <StudentAppointmentCreateModal />
        </div>
    </div>
</template>