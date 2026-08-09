import { OpenClinicRow } from '@/keys/schedules/openClinicsManagementKeys';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

export function useOpenClinics() {
    const loading = ref(false);
    const clinics = ref<OpenClinicRow[]>([]);

    const search = ref('');

    const page = ref(1);
    const perPage = ref(6);

    const total = ref(0);
    const totalPages = ref(0);

    const fromTo = computed(() => {
        const from = (page.value - 1) * perPage.value + 1;
        const to = Math.min(
            page.value * perPage.value,
            total.value,
        );

        return total.value
            ? `${from}-${to} de ${total.value}`
            : '0';
    });

    async function loadClinics() {
        loading.value = true;

        try {
                const response = await axios.get(
                '/schedules/open-clinics/table',
                {
                        params: {
                        page: page.value,
                        per_page: perPage.value,
                        search: search.value || undefined,
                        },
                },
                );

                console.log(
                'OPEN CLINICS RESPONSE:',
                response.data,
                );

                clinics.value = response.data.data;
                total.value = response.data.total;
                totalPages.value = response.data.last_page;
        } finally {
                loading.value = false;
        }
        }

    function goToPage(newPage: number) {
        if (
            newPage >= 1 &&
            newPage <= totalPages.value
        ) {
            page.value = newPage;
        }
    }

    let searchTimeout: number;

    watch(search, () => {
        clearTimeout(searchTimeout);

        searchTimeout = window.setTimeout(() => {
            page.value = 1;
            loadClinics();
        }, 400);
    });

    watch(
        [page, perPage],
        loadClinics,
    );

    return {
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
    };
}