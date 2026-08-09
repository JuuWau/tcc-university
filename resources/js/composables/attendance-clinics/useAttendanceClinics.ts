import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import type { AttendanceClinic } from '@/types/attendance/attendance';

export function useAttendanceClinics() {
    const loading = ref(false);

    const clinics = ref<AttendanceClinic[]>([]);
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
                '/attendance/clinics/table',
                {
                    params: {
                    page: page.value,
                    per_page: perPage.value,
                    search: search.value || undefined,
                    },
                },
            );

            clinics.value = data.data;
            total.value = data.meta.total;
            totalPages.value = data.meta.last_page;
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
            if (page.value === 1) {
                loadClinics();
                return;
            }

            page.value = 1;
        }, 400);
    });

    watch(page, (newPage, oldPage) => {
        if (newPage !== oldPage) {
            loadClinics();
        }
    });

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