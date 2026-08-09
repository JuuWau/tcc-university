import { ClinicManagementRow } from '@/types/clinics-management/clinicManagement';
import axios from 'axios';
import { ref, computed, watch } from 'vue';

export function useClinics() {
    const loading = ref(false);
    const clinics = ref<ClinicManagementRow[]>([]);
    const search = ref('');

    const page = ref(1);
    const perPage = ref(6);
    const total = ref(0);
    const totalPages = ref(0);

    let searchTimeout: number;

    const fromTo = computed(() => {
        const f = (page.value - 1) * perPage.value + 1;
        const t = Math.min(
            page.value * perPage.value,
            total.value,
        );

        return total.value
            ? `${f}-${t} de ${total.value}`
            : '0';
    });

    async function loadClinics() {
        loading.value = true;

        try {
            const { data } = await axios.get<{
                data: any[];
                meta: {
                    current_page: number;
                    last_page: number;
                    per_page: number;
                    total: number;
                    from: number | null;
                    to: number | null;
                };
            }>('/clinics-management/clinicsTable', {
                params: {
                    page: page.value,
                    per_page: perPage.value,
                    search: search.value,
                },
            });

            clinics.value = data.data;
            total.value = data.meta.total;
            totalPages.value = data.meta.last_page;
        } catch {
            clinics.value = [];
            total.value = 0;
            totalPages.value = 0;
        } finally {
            loading.value = false;
        }
    }

    function goToPage(p: number) {
        if (p >= 1 && p <= totalPages.value) {
            page.value = p;
        }
    }

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
        {
            deep: false,
        },
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