import axios from 'axios';
import { ref } from 'vue';
import type { PatientSchedule } from '@/types/patient/patientSchedule';

export function usePatientSchedules(patientId: number) {
    const upcomingAppointments = ref<PatientSchedule[]>([]);
    const completedAppointments = ref<PatientSchedule[]>([]);
    const loading = ref(false);

    async function loadSchedules() {
        loading.value = true;

        try {
            const response = await axios.get(
                `/patients/${patientId}/schedules`,
            );

            upcomingAppointments.value = response.data.upcoming ?? [];
            completedAppointments.value = response.data.completed ?? [];
        } catch (error) {
            console.error('Erro ao carregar agendamentos:', error);

            upcomingAppointments.value = [];
            completedAppointments.value = [];
        } finally {
            loading.value = false;
        }
    }

    return {
        upcomingAppointments,
        completedAppointments,
        loading,
        loadSchedules,
    };
}