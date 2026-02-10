<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <StudentCreateModal />

                        <StudentDeleteModal />

                        <StudentDeactivateModal />

                        <StudentActivationModal />

                        <StudentsTable
                            @edit="openEditModal"
                            @delete="openDeleteModal"
                            @create="openCreateModal"
                            @deactivate="openDeactivateModal"
                            @activate="openActivationModal"
                            @resend="resendInvite"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    RefreshTableKey,
    StudentActivateKey,
    StudentCreateKey,
    StudentDeactivateKey,
    StudentDeleteKey,
    StudentEditKey,
} from '@/keys/students/studentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import StudentActivationModal from '@/pages/students/components/StudentActivationModal.vue';
import StudentDeactivateModal from '@/pages/students/components/StudentDeactivateModal.vue';
import StudentDeleteModal from '@/pages/students/components/StudentDeleteModal.vue';
import StudentCreateModal from '@/pages/students/components/StudentsCreateModal.vue';

import StudentsTable from '@/pages/students/StudentsTable.vue';
import { Student } from '@/types/student/student';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { provide, ref } from 'vue';
import { toast } from 'vue3-toastify';
defineProps<{
    periods: Array<{
        id: number;
        academic_year: string;
        semester: string;
        calendar_year: string;
    }>;
}>();

const loading = ref(false);
const refreshTableRef = ref<(() => void) | null>(null);

const createModal = { isOpen: ref(false) };
const editModal = {
    isOpen: ref(false),
    student: ref<Student | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    student: ref<Student | null>(null),
};
const deactivateModal = {
    isOpen: ref(false),
    student: ref<Student | null>(null),
};

const activationModal = {
    isOpen: ref(false),
    student: ref<Student | null>(null),
};

provide(RefreshTableKey, refreshTableRef);
provide(StudentEditKey, editModal);
provide(StudentDeleteKey, deleteModal);
provide(StudentDeactivateKey, deactivateModal);
provide(StudentActivateKey, activationModal);
provide(StudentCreateKey, createModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openEditModal(student: Student) {
    router.visit(`/students/${student.id}`);
}

function openDeleteModal(student: Student) {
    deleteModal.student.value = student;
    deleteModal.isOpen.value = true;
}

function openDeactivateModal(student: Student) {
    deactivateModal.student.value = student;
    deactivateModal.isOpen.value = true;
}

function openActivationModal(student: Student) {
    activationModal.student.value = student;
    activationModal.isOpen.value = true;
}

async function resendInvite(student: Student) {
    if (loading.value) return;

    try {
        loading.value = true;

        await axios.post(`/students/resend-invite/${student.id}`);

        toast.success('Convite reenviado com sucesso');
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao reenviar convite',
        );
    } finally {
        loading.value = false;
    }
}
</script>
