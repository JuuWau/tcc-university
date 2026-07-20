import { ref } from 'vue';
import { CLINIC_PATIENT_STATUS, type ClinicPatientStatus, } from '@/types/clinics-management/clinicManagement';
import { toast } from 'vue3-toastify';
import axios from 'axios';

export function useClinicManagement() {
    const loading = ref(false);
    const page = ref(1);
    const perPage = ref(10);
    const total = ref(0);
    const totalPages = ref(0);
    const activeStatus =
        ref<ClinicPatientStatus>(
            CLINIC_PATIENT_STATUS.WAITING,
        );

    const patients = ref([]);

    async function loadPatients(clinicId: number) {
        loading.value = true;
        console.log(clinicId);
        try {
            const response = await axios.get(
                `/clinics-management/${clinicId}/table`,
                {
                    params: {
                        page: page.value,
                        per_page: perPage.value,
                        status: activeStatus.value,
                    },
                }
            );

            patients.value = response.data.data;
            total.value = response.data.meta.total;
            totalPages.value = response.data.meta.last_page;
        } finally {
            loading.value = false;
        }
    }

    function setStatus(
        status: ClinicPatientStatus,
    ) {
        activeStatus.value = status;
    }

    function goToPage(newPage: number) {
        if (
            newPage >= 1 &&
            newPage <= totalPages.value
        ) {
            page.value = newPage;
        }
    }

    return {
        loading,
        activeStatus,
        patients,
        page,
        perPage,
        total,
        totalPages,
        loadPatients,
        setStatus,
        goToPage
    };
}