<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { PatientScheduleBookingContextKey } from '@/keys/patients/patientScheduleBookingKeys.js';
import { PatientTabContextKey } from '@/keys/patients/patientKeys.js';
import { usePatientScheduleBooking } from '@/composables/patient/usePatientScheduleBooking.js';
import { usePatientSchedules } from '@/composables/patient/usePatientSchedules.js';
import { inject, onMounted, provide, ref } from 'vue';

import PatientScheduleSection from './components/PatientScheduleSection.vue';
import PatientScheduleCard from './components/PatientScheduleCard.vue';
import PatientScheduleBooking from './components/PatientScheduleBooking.vue';
import { ArrowLeft } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const can = (permission: string) => {
    return page.props.auth.permissions.includes(permission);
};

const patientContext = inject(PatientTabContextKey);

if (!patientContext) {
    throw new Error('PatientTabContext não foi fornecido.');
}

const patientId = patientContext.patient.value.id;
const patientName = patientContext.patient.value.name;

const upcomingOpen = ref(false);
const completedOpen = ref(false);
const bookingOpen = ref(false);

const {
    upcomingAppointments,
    completedAppointments,
    loading,
    loadSchedules,
} = usePatientSchedules(patientId);

const booking = usePatientScheduleBooking(patientId, patientName);

provide(PatientScheduleBookingContextKey, booking);

onMounted(async () => {
    await loadSchedules();
});

function startBooking() {
    bookingOpen.value = true;
}

function cancelBooking() {
    bookingOpen.value = false;
}
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <PageHeader
                title="Agendamentos"
                description="Consulte e gerencie os agendamentos do paciente."
            >
                <template #actions>
                    <Button
                        v-if="!bookingOpen && can('patients.personal-page.manageAppointments')"
                        class="cursor-pointer rounded-xl bg-sky-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700"
                        @click="startBooking"
                    >
                        + Gerenciar Atendimentos
                    </Button>

                    <Button 
                        v-else-if="bookingOpen && can('patients.personal-page.manageAppointments')" 
                        variant="outline"
                        class="w-full sm:w-auto" 
                        @click="cancelBooking">
                            <ArrowLeft class="h-4 w-4" /> Voltar
                    </Button>
                </template>
            </PageHeader>

            <div class="mt-6">
                <PatientScheduleBooking
                    v-if="bookingOpen"
                />

                <div
                    v-else
                    class="space-y-4"
                >
                    <PatientScheduleSection
                        v-model:open="upcomingOpen"
                        title="Próximos agendamentos"
                        description="Horários futuros do paciente"
                        :count="upcomingAppointments.length"
                    >
                        <div
                            v-if="loading"
                            class="py-4 text-sm text-gray-500"
                        >
                            Carregando agendamentos...
                        </div>

                        <div
                            v-else-if="
                                upcomingAppointments.length === 0
                            "
                            class="py-4 text-sm text-gray-500"
                        >
                            Nenhum próximo agendamento.
                        </div>

                        <div
                            v-else
                            class="space-y-3"
                        >
                            <PatientScheduleCard
                                v-for="appointment in upcomingAppointments"
                                :key="appointment.id"
                                :appointment="appointment"
                            />
                        </div>
                    </PatientScheduleSection>

                    <PatientScheduleSection
                        v-model:open="completedOpen"
                        title="Agendamentos realizados"
                        description="Histórico de atendimentos do paciente"
                        :count="completedAppointments.length"
                    >
                        <div
                            v-if="loading"
                            class="py-4 text-sm text-gray-500"
                        >
                            Carregando agendamentos...
                        </div>

                        <div
                            v-else-if="
                                completedAppointments.length === 0
                            "
                            class="py-4 text-sm text-gray-500"
                        >
                            Nenhum agendamento realizado.
                        </div>

                        <div
                            v-else
                            class="space-y-3"
                        >
                            <PatientScheduleCard
                                v-for="appointment in completedAppointments"
                                :key="appointment.id"
                                :appointment="appointment"
                            />
                        </div>
                    </PatientScheduleSection>
                </div>
            </div>
        </div>
    </div>
</template>