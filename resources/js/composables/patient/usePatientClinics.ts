import { ref } from 'vue';
import axios from 'axios';

import { PATIENT_CLINIC_STATUS, type PatientClinicStatus, type PatientClinicRow, } from '@/types/patient/patientClinics';

export function usePatientClinics() {
    const loading = ref(false);
    const page = ref(1);
    const perPage = ref(10);
    const total = ref(0);
    const totalPages = ref(0);

    const activeStatus =
        ref<PatientClinicStatus>(
            PATIENT_CLINIC_STATUS.WAITING,
        );

    const clinics = ref<PatientClinicRow[]>([]);

    async function loadClinics(patientId: number,) 
    {
        loading.value = true;

        try {
            const response = await axios.get(
                `/patients/${patientId}/clinics/table`,
                {
                    params: {
                        page: page.value,
                        per_page: perPage.value,
                        status: activeStatus.value,
                    },
                }
            );

            clinics.value =
                response.data.data;

            total.value =
                response.data.meta.total;

            totalPages.value =
                response.data.meta.last_page;
        } finally {
            loading.value = false;
        }
    }

    function setStatus(status: PatientClinicStatus,) 
    {
        page.value = 1;
        activeStatus.value = status;
    }

    function goToPage(newPage: number,) 
    {
        if (
            newPage >= 1 &&
            newPage <= totalPages.value
        ) {
            page.value = newPage;
        }
    }

    return {
        loading,
        clinics,
        activeStatus,
        page,
        perPage,
        total,
        totalPages,
        loadClinics,
        setStatus,
        goToPage,
    };
}