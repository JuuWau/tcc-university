<template>
    <AppLayout>
        <div class="w-full space-y-8 p-8">
            <StudentHeader />

            <nav class="flex flex-wrap gap-2 rounded-2xl bg-gray-100 p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="
                        activeTab === tab.key
                            ? 'bg-white text-sky-600 shadow'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                >
                    {{ tab.label }}
                </button>
            </nav>

            <div>
                <StudentPersonalData v-if="activeTab === 'personal'" />
                <StudentScheduleTab v-if="activeTab === 'calendar'" />
                <StudentActionLogs v-if="activeTab === 'logs'" />
            </div>
        </div>

        <StudentsEditModal @updated="onStudentUpdated" />
        <StudentAcademicDataEditModal @updated="onAcademicDataUpdated" />
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import StudentAcademicDataEditModal from '@/pages/students/components/StudentAcademicDataEditModal.vue';
import StudentsEditModal from '@/pages/students/components/StudentsEditModal.vue';
import StudentHeader from '@/pages/students/StudentHeader.vue';
import StudentPersonalData from '@/pages/students/tabs/StudentPersonalData.vue';
import StudentActionLogs from '@/pages/users/tabs/UserActionLogs.vue';
import { StudentTabContextKey } from '@/keys/students/studentKeys';
// import StudentCalendar from '@/pages/students/tabs/StudentCalendar.vue';
import type { Student } from '@/types/student/student';
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import StudentScheduleTab from './tabs/StudentScheduleTab.vue';
import { useUserActionLogs } from '@/composables/user/useUserActionLogs.js';
import { UserActionLogsContextKey } from '@/keys/action-logs/userActionLogsKeys.js';

const page = usePage();
const student = computed(
    () => (page.props as unknown as { student: Student }).student,
);

const editModalOpen = ref(false);
const academicDataEditModalOpen = ref(false);
provide(StudentTabContextKey, {
    student,
    editModalOpen,
    academicDataEditModalOpen,
});


const userId = computed(() => student.value.user?.id ?? 0);

const actionLogs = useUserActionLogs(
    'students',
    computed(() => student.value.id),
);

provide(UserActionLogsContextKey, actionLogs);

const activeTab = ref<'personal' | 'calendar' | 'logs'>('personal');

const tabs: {
    key: 'personal' | 'calendar' | 'logs';
    label: string;
}[] = [
    { key: 'personal', label: 'Dados pessoais' },
    { key: 'calendar', label: 'Agenda' },
    { key: 'logs', label: 'Histórico de ações' },
];

function onStudentUpdated() {
    editModalOpen.value = false;
    void router.reload();
}

function onAcademicDataUpdated() {
    academicDataEditModalOpen.value = false;
    void router.reload();
}

watch(activeTab, async (tab) => {
    if (
        tab === 'logs' &&
        actionLogs.logs.value.data.length === 0
    ) {
        await actionLogs.load();
    }
});
</script>
