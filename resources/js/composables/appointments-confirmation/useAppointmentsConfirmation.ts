import { Ref, ref } from 'vue';

import type {
    AppointmentsConfirmationFilters,
    AppointmentConfirmation
} from '@/types/appointments-confirmation/appointmentsConfirmation';
import { toast } from 'vue3-toastify';
import axios from 'axios';

export function useAppointmentsConfirmation(appointments: Ref<AppointmentConfirmation[]>, filters: Ref<AppointmentsConfirmationFilters>) {
    const loading = ref(false);

    async function searchAppointments() {
        loading.value = true;
        
        try {
            const hasFilters =
                filters.value.date ||
                filters.value.clinic_id ||
                filters.value.period_id ||
                filters.value.status;

            if (!hasFilters) {
                appointments.value = [];
                return;
            }
            const response = await axios.get(
                '/appointments-confirmation/list',
                {
                    params: {
                        date: filters.value.date,
                        clinic_id: filters.value.clinic_id,
                        period_id: filters.value.period_id,
                        status: filters.value.status,
                    },
                }
            );

            appointments.value = response.data.appointments;

        } catch (error) {
            toast.error(
                'Erro ao buscar agendamentos.'
            );
        } finally {
            loading.value = false;
        }
    }

    async function updateAppointmentStatus(id: number, status: string) 
    {
        try {
            const response = await axios.patch(
                `/appointments-confirmation/${id}/status`,
                {
                    status,
                }
            );

            toast.success(response.data.message);

            const index = appointments.value.findIndex(
                appointment => appointment.id === id
            );

            if (index !== -1) {
                appointments.value[index].status = status;
            }

        } catch (error) {
            toast.error('Erro ao atualizar status.');
        }
    }

    return {
        appointments,
        filters,
        loading,
        searchAppointments,
        updateAppointmentStatus,
    };
}