import { computed, ref } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import type { ClinicReport, ClinicReportFilters, ClinicReportPeriod, ClinicReportSummary, } from '@/types/clinics-report/clinicsReport';

export function useClinicsReport() {
    const pageProps = usePage();

    const clinics = ref<ClinicReport[]>([]);

    const periods = ref<ClinicReportPeriod[]>(
        (pageProps.props.filters?.periods ?? []) as ClinicReportPeriod[],
    );

    const summary = ref<ClinicReportSummary>({
        total: 0,
        active: 0,
        inactive: 0,
        with_schedule: 0,
        without_schedule: 0,
    });

    const filters = ref<ClinicReportFilters>({
        search: '',
        period_id: null,
        status: null,
    });

    const loading = ref(false);

    const page = ref(1);

    const perPage = ref(15);

    const total = ref(0);

    const totalPages = ref(0);

    const hasActiveFilters = computed(() => {
        return Boolean(
            filters.value.search ||
            filters.value.period_id ||
            filters.value.status,
        );
    });

    const activeFiltersCount = computed(() => {
        return [
            filters.value.search,
            filters.value.period_id,
            filters.value.status,
        ].filter(Boolean).length;
    });

    async function loadClinics() {
        loading.value = true;

        try {
            const response = await axios.get(
                '/reports/clinics/data',
                {
                    params: {
                        ...filters.value,
                        page: page.value,
                        per_page: perPage.value,
                    },
                },
            );

            clinics.value = response.data.clinics.data;

            page.value =
                response.data.clinics.current_page;

            total.value =
                response.data.clinics.total;

            totalPages.value =
                response.data.clinics.last_page;

            summary.value = response.data.summary;
        } finally {
            loading.value = false;
        }
    }

    function search() {
        page.value = 1;

        loadClinics();
    }

    function clearFilters() {
        filters.value = {
            search: '',
            period_id: null,
            status: null,
        };

        page.value = 1;

        loadClinics();
    }

    function goToPage(newPage: number) {
        if (
            newPage < 1 ||
            newPage > totalPages.value ||
            newPage === page.value
        ) {
            return;
        }

        page.value = newPage;

        loadClinics();
    }

    async function exportExcel() {
        const response = await axios.get(
            '/reports/clinics/export',
            {
                params: {
                    ...filters.value,
                },
                responseType: 'blob',
            },
        );

        const blob = new Blob(
            [response.data],
            {
                type: response.headers['content-type'],
            },
        );

        const url =
            window.URL.createObjectURL(blob);

        const link =
            document.createElement('a');

        link.href = url;

        link.download =
            'relatorio-clinicas.xlsx';

        document.body.appendChild(link);

        link.click();

        link.remove();

        window.URL.revokeObjectURL(url);
    }

    loadClinics();

    return {
        clinics,
        periods,
        summary,
        filters,
        loading,
        page,
        perPage,
        total,
        totalPages,
        hasActiveFilters,
        activeFiltersCount,
        loadClinics,
        search,
        clearFilters,
        goToPage,
        exportExcel,
    };
}