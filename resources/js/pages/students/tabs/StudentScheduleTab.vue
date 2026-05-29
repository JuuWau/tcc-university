<script setup lang="ts">
import { inject, onMounted, provide } from 'vue';
import { useStudentSchedule } from '@/composables/students/useStudentSchedule';
import { StudentScheduleContextKey } from '@/keys/students/studentScheduleKeys';
import StudentScheduleToolbar from './components/StudentScheduleToolbar.vue';
import StudentScheduleCalendar from './components/StudentScheduleCalendar.vue';
import StudentScheduleDayEvents from './components/StudentScheduleDayEvents.vue';
import { StudentTabContext, StudentTabContextKey } from '@/keys/students/studentKeys.js';

const ctx = inject(StudentTabContextKey) as StudentTabContext;

const studentId = ctx.student.value.id;

const schedule = useStudentSchedule(studentId);

provide(StudentScheduleContextKey, schedule);
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

            <StudentScheduleDayEvents />
        </div>
    </div>
</template>