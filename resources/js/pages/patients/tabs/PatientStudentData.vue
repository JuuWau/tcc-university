<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Dados do estudante
                </h2>
                <p class="text-sm text-gray-500">
                    Estudante vinculado ao paciente
                </p>
            </div>

            <Button
                variant="outline"
                class="cursor-pointer"
                size="sm"
                @click="openEditModal"
            >
                <Pencil class="mr-2 h-4 w-4" />
                Editar
            </Button>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">
                    Estudante
                </p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ patient?.student?.name ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">
                    ID do estudante
                </p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ patient?.student_id ?? '—' }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PatientTabContext } from '@/keys/patients/patientKeys';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import { Pencil } from 'lucide-vue-next';
import { inject } from 'vue';

const ctx = inject(PatientTabContextKey) as PatientTabContext | undefined;

if (!ctx) {
    throw new Error(
        'PatientStudentData must be used inside a PatientTab (provide PatientTabContextKey).',
    );
}

const { patient, editStudentModalOpen } = ctx;

function openEditModal() {
    editStudentModalOpen.value = true;
}
</script>
