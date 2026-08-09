<script setup lang="ts">
import CreateButton from '@/components/buttons/CreateButton.vue';
import { useOpenClinics } from '@/composables/schedules/useOpenClinicsManagement';
import {
    OpenClinicsManagementKey,
    type OpenClinicRow,
} from '@/keys/schedules/openClinicsManagementKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import OpenClinicCard from '@/pages/schedules/components/OpenClinicCard.vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { onMounted, provide, ref, watch } from 'vue';

const props = defineProps<{
    clinics: OpenClinicRow[];
}>();

const clinicsRef = ref<OpenClinicRow[]>([...(props.clinics ?? [])]);

const {
    loading,
    clinics,
    search,
    page,
    perPage,
    total,
    totalPages,
    fromTo,
    loadClinics,
    goToPage,
} = useOpenClinics();

onMounted(() => {
    loadClinics();
});

watch(
    () => props.clinics,
    (next) => {
        clinicsRef.value = [...(next ?? [])];
    },
    { deep: true },
);

function goManageClinic(clinicId: number) {
    router.visit(`/schedules/open-clinics/${clinicId}`, {
        preserveScroll: true,
    });
}

function goOpenSchedule() {
    router.visit('/schedules/open-schedule', {
        preserveScroll: true,
    });
}

provide(OpenClinicsManagementKey, {
    clinics: clinicsRef,
    goManageClinic,
    goOpenSchedule,
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-10 w-full max-w-6xl px-4">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div
                    class="flex flex-col gap-3 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-xl font-semibold tracking-tight text-gray-900"
                        >
                            Gerenciar clínicas com agenda aberta
                        </h1>
                        <p class="text-sm text-gray-500">
                            Visualize as clínicas que possuem dias abertos e
                            acompanhe a próxima disponibilidade.
                        </p>
                    </div>
                    <CreateButton
                        icon="Plus"
                        label="Abrir novas agendas"
                        @click="goOpenSchedule"
                    />
                </div>

                <div class="relative w-full border-b border-gray-200 pb-4">
                    <Search class="pointer-events-none absolute left-3 top-1/3 h-4 w-4 -translate-y-1/2 text-gray-400"/>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar clínica..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none"
                    />
                </div>

                <div
                    v-if="!clinics.length"
                    class="rounded border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600"
                >
                    Nenhuma clínica com agenda aberta no momento.
                </div>

                <div
                    v-else
                    class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <OpenClinicCard
                        v-for="clinic in clinics"
                        :key="clinic.clinic_id"
                        :clinic="clinic"
                    />
                </div>
                <div
                    v-if="totalPages > 0"
                    class="mt-4 flex flex-wrap items-center justify-between gap-2"
                >
                    <p class="text-sm text-gray-600">
                        {{ fromTo }}
                    </p>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            :disabled="page <= 1"
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
                            :disabled="page >= totalPages"
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
