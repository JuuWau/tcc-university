<script setup lang="ts">
import { computed, provide, reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

import type { AppPageProps } from '@/types';

import type {
    AppointmentConfirmation,
    ClinicOption,
    PeriodOption,
    AppointmentsConfirmationFilters,
} from '@/types/appointments-confirmation/appointmentsConfirmation';

import {
    AppointmentsConfirmationKey,
} from '@/keys/appointments-confirmation/appointmentsConfirmationKeys';
import { useAppointmentsConfirmation } from '@/composables/appointments-confirmation/useAppointmentsConfirmation';
import AppointmentsConfirmationFiltersComponent from './components/AppointmentsConfirmationFilters.vue';
import AppointmentsConfirmationTable from './AppointmentsConfirmationTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';


type AppointmentsConfirmationPage = AppPageProps<{
    appointments: AppointmentConfirmation[];
    clinics: ClinicOption[];
    periods: PeriodOption[];
    filters: AppointmentsConfirmationFilters;
}>;


const page = usePage<AppointmentsConfirmationPage>();

const loading = ref(false);

const filters = ref<AppointmentsConfirmationFilters>({
    clinic_id: page.props.filters.clinic_id ?? null,
    period_id: page.props.filters.period_id ?? null,
    date: page.props.filters.date ?? null,
    status: page.props.filters.status ?? null,
});


const appointments = ref<AppointmentConfirmation[]>(
    page.props.appointments ?? []
);

const clinics = computed(
    () => page.props.clinics ?? []
);

const periods = computed(
    () => page.props.periods ?? []
);

const {
    updateAppointmentStatus, searchAppointments,
} = useAppointmentsConfirmation(appointments, filters);

function clearFilters() {

    filters.value = {
        clinic_id: null,
        period_id: null,
        date: null,
        status: null,
    };

    searchAppointments();
}

provide(
    AppointmentsConfirmationKey,
    {
        appointments,
        clinics,
        periods,
        filters,
        loading,
        searchAppointments,
        clearFilters,
        updateAppointmentStatus,
    }
);

</script>
<template>
    <AppLayout>
        <div class="mt-10 mb-10 flex justify-center">
            <div class="w-full max-w-6xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="px-6">
                        <div class="border-b border-gray-200 pb-6">
                            <h1 class="text-xl pt-6 pb-1 font-semibold tracking-tight text-gray-900">
                                Confirmação de agenda
                            </h1>
                            <p class="text-sm text-gray-500">
                                Gerencie as confirmações de agenda cadastradas no sistema
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <AppointmentsConfirmationFiltersComponent />

                        <AppointmentsConfirmationTable />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>