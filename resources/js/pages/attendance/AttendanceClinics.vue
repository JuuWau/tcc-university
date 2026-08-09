<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceClinicCard from './components/AttendanceClinicCard.vue';
import { router } from '@inertiajs/vue3';
import { provide, ref, watch } from 'vue';
import { AttendanceManagementKey, } from '@/keys/attendance/attendanceManagementKeys';
import type { AttendanceClinic } from '@/types/attendance/attendance';
import { Search } from 'lucide-vue-next';
import { useAttendanceClinics } from '@/composables/attendance-clinics/useAttendanceClinics.js';

const {
    loading,
    clinicsRef,
    search,
    page,
    totalPages,
    fromTo,
    goToPage,
} = useAttendanceClinics();


function goManageClinic(clinicId: number) {
    router.visit(`/attendance/clinics/${clinicId}`, {
        preserveScroll: true,
    });
}

provide(AttendanceManagementKey, {
    clinics: clinicsRef,
    goManageClinic,
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-10 w-full max-w-6xl px-4">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="pb-4">
                    <h1
                        class="text-xl font-semibold tracking-tight text-gray-900"
                    >
                        Controle de Comparecimento
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Selecione uma clínica para gerenciar o comparecimento
                        dos alunos.
                    </p>
                </div>

                <div class="relative w-full border-b border-gray-200 pb-4">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar clínica..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    />
                </div>

                <div
                    v-if="loading"
                    class="mt-4 rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
                >
                    Carregando clínicas...
                </div>

                <div
                    v-else-if="!clinicsRef.length"
                    class="mt-4 rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
                >
                    Nenhuma clínica disponível para gerenciamento de comparecimento.
                </div>

                <div
                    v-else
                    class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <AttendanceClinicCard
                        v-for="clinic in clinicsRef"
                        :key="clinic.clinic_id"
                        :clinic="clinic"
                    />
                </div>

                <div
                    v-if="!loading && totalPages > 0"
                    class="mt-4 flex flex-wrap items-center justify-between gap-2"
                >
                    <p class="text-sm text-gray-600">
                        {{ fromTo }}
                    </p>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            :disabled="page <= 1 || loading"
                            @click="goToPage(page - 1)"
                            class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                        >
                            Anterior
                        </button>

                        <template
                            v-for="p in totalPages"
                            :key="p"
                        >
                            <button
                                v-if="
                                    p === 1 ||
                                    p === totalPages ||
                                    (p >= page - 2 && p <= page + 2)
                                "
                                type="button"
                                :disabled="loading"
                                @click="goToPage(p)"
                                :class="[
                                    'rounded-md px-3 py-1.5 text-sm transition',
                                    p === page
                                        ? 'bg-sky-600 text-white shadow'
                                        : 'text-gray-600 hover:bg-gray-100',
                                ]"
                            >
                                {{ p }}
                            </button>

                            <span
                                v-else-if="
                                    p === page - 3 ||
                                    p === page + 3
                                "
                                class="px-1"
                            >
                                …
                            </span>
                        </template>

                        <button
                            type="button"
                            :disabled="page >= totalPages || loading"
                            @click="goToPage(page + 1)"
                            class="rounded border border-gray-300 bg-white px-3 py-1 text-sm hover:bg-gray-50 disabled:opacity-50"
                        >
                            Próxima
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>