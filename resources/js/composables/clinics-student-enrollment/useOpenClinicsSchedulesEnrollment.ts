import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { OpenClinicScheduleRow } from '@/keys/schedule-enrollment/openClinicsScheduleManagementKeys';

export function useOpenClinicsSchedulesEnrollment() {
    const loading = ref(false);

    const clinics = ref<OpenClinicScheduleRow[]>([]);
    const search = ref('');

    const page = ref(1);
    const perPage = ref(12);
    const total = ref(0);
    const totalPages = ref(0);

    const clinicsRef = computed(() => clinics.value);

    const fromTo = computed(() => {
        if (!total.value) {
            return '0';
        }

        const from =
            (page.value - 1) * perPage.value + 1;

        const to = Math.min(
            page.value * perPage.value,
            total.value,
        );

        return `${from}-${to} de ${total.value}`;
    });

    async function loadClinics() {
        loading.value = true;

        try {
            const { data } = await axios.get(
                '/schedule-enrollment/open-clinics/table',
                {
                    params: {
                        page: page.value,
                        per_page: perPage.value,
                        search: search.value || undefined,
                    },
                },
            );

            clinics.value = data.data;
            total.value = data.total;
            totalPages.value = data.last_page;
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

    let searchTimeout: number | undefined;

    watch(search, () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = window.setTimeout(() => {
            page.value = 1;
            loadClinics();
        }, 400);
    });

    watch(
        [page, perPage],
        ([newPage], [oldPage]) => {
            if (newPage !== oldPage) {
                loadClinics();
            }
        },
    );

    onMounted(() => {
        loadClinics();
    });

    return {
        loading,
        clinics,
        clinicsRef,
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