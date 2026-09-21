import { ref } from 'vue';
import axios from 'axios';
import type { PrePatient } from '@/types/pre-patients/prePatient';

export function usePrePatients() {
    const loading = ref(false);
    const page = ref(1);
    const perPage = ref(10);
    const total = ref(0);
    const totalPages = ref(0);
    const search = ref('');
    const activeStatus = ref<string | null>(null);
    const activeClinicId = ref<number | null>(null);
    const prePatients = ref<PrePatient[]>([]);

    async function loadPrePatients() {
        loading.value = true;

        try {
            const response = await axios.get(
                '/pre-patients/table',
                {
                    params: {
                        page: page.value,
                        per_page: perPage.value,
                        search: search.value || null,
                        status: activeStatus.value,
                        clinic_id: activeClinicId.value,
                    },
                }
            );

            prePatients.value =
                response.data.data;

            total.value =
                response.data.meta.total;

            totalPages.value =
                response.data.meta.last_page;
        } finally {
            loading.value = false;
        }
    }

    function setSearch(value: string) {
        page.value = 1;
        search.value = value;
    }

    function setStatus(status: string | null) {
        page.value = 1;
        activeStatus.value = status;
    }

    function setClinic(clinicId: number | null) {
        page.value = 1;
        activeClinicId.value = clinicId;
    }

    function goToPage(newPage: number) {
        if (
            newPage >= 1 &&
            newPage <= totalPages.value
        ) {
            page.value = newPage;
        }
    }

    function resetFilters() {
        page.value = 1;
        search.value = '';
        activeStatus.value = null;
        activeClinicId.value = null;
    }

    return {
        loading,
        prePatients,
        search,
        activeStatus,
        activeClinicId,
        page,
        perPage,
        total,
        totalPages,
        loadPrePatients,
        setSearch,
        setStatus,
        setClinic,
        goToPage,
        resetFilters,
    };
}