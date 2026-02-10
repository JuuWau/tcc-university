<template>
    <AppLayout>
        <div class="w-full space-y-8 p-8">
            <!-- Header -->
            <StudentHeader />

            <!-- Tabs -->
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

            <!-- Conteúdo -->
            <div>
                <StudentPersonalData v-if="activeTab === 'personal'" />

                <div
                    v-else-if="activeTab === 'academic'"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-gray-500">Conteúdo acadêmico em breve.</p>
                </div>
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

import { StudentTabContextKey } from '@/keys/students/studentKeys';
import type { Student } from '@/types/student/student';
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref } from 'vue';

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

const activeTab = ref<'personal' | 'academic' | 'calendar' | 'logs'>(
    'personal',
);

const tabs: {
    key: 'personal' | 'academic' | 'calendar' | 'logs';
    label: string;
}[] = [
    { key: 'personal', label: 'Dados pessoais' },
    { key: 'academic', label: 'Acadêmico' },
    { key: 'calendar', label: 'Agenda' },
    { key: 'logs', label: 'Logs' },
];

function onStudentUpdated() {
    editModalOpen.value = false;
    void router.reload();
}

function onAcademicDataUpdated() {
    academicDataEditModalOpen.value = false;
    void router.reload();
}
</script>
