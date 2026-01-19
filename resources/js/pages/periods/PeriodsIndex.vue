<template>
    <AppLayout>
        <div class="mt-25 flex justify-center">
            <div class="w-full max-w-7xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <PeriodEditModal />

                        <PeriodCreateModal />

                        <PeriodDeleteModal />

                        <PeriodsTable
                            @edit="openEditModal"
                            @delete="openDeleteModal"
                            @create="openCreateModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {
    PeriodCreateKey,
    PeriodDeleteKey,
    PeriodEditKey,
    PeriodsGroupKey,
} from '@/keys/periods/periodKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import AppLayout from '@/layouts/AppLayout.vue';
import PeriodsTable from '@/pages/periods/PeriodsTable.vue';
import PeriodCreateModal from '@/pages/periods/components/PeriodCreateModal.vue';
import PeriodDeleteModal from '@/pages/periods/components/PeriodDeleteModal.vue';
import PeriodEditModal from '@/pages/periods/components/PeriodEditModal.vue';
import { Period } from '@/types/period';
import { provide, ref } from 'vue';
const { periods } = defineProps({
    periods: Array,
});

const periodsRef = ref(periods);
const loading = ref(false);

const createModal = { isOpen: ref(false) };
const editModal = {
    isOpen: ref(false),
    period: ref<Period | null>(null),
};
const deleteModal = {
    isOpen: ref(false),
    period: ref<Period | null>(null),
};

provide(PeriodsGroupKey, periodsRef);
provide(PeriodEditKey, editModal);
provide(PeriodDeleteKey, deleteModal);
provide(PeriodCreateKey, createModal);
provide(LoadingKey, loading);

function openCreateModal() {
    createModal.isOpen.value = true;
}

function openEditModal(period: Period) {
    editModal.period.value = period;
    editModal.isOpen.value = true;
}

function openDeleteModal(period: Period) {
    deleteModal.period.value = period;
    deleteModal.isOpen.value = true;
}
</script>
