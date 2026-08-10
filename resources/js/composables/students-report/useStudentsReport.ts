import { computed, ref } from 'vue';
import axios from 'axios';
import type { StudentReport, StudentReportFilters, StudentReportPeriod, StudentReportSummary, } from '@/types/students-report/studentsReport';
import { usePage } from '@inertiajs/vue3';

export function useStudentsReport() {
    const pageProps = usePage();
    const students = ref<StudentReport[]>([]);
    const periods = ref<StudentReportPeriod[]>(
        (pageProps.props.filters?.periods ?? []) as StudentReportPeriod[],
    );

    const summary = ref<StudentReportSummary>({
        total: 0,
        active: 0,
        inactive: 0,
        invitation_accepted: 0,
        invitation_pending: 0,
    });

    const filters = ref<StudentReportFilters>({
        search: '',
        period_id: null,
        status: null,
        invitation_status: null,
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
            filters.value.status ||
            filters.value.invitation_status,
        );
    });

    const activeFiltersCount = computed(() => {
        return [
            filters.value.search,
            filters.value.period_id,
            filters.value.status,
            filters.value.invitation_status,
        ].filter(Boolean).length;
    });

    async function loadStudents() {
        loading.value = true;

        try {
            const response = await axios.get('/reports/students/data', {
                params: {
                    ...filters.value,
                    page: page.value,
                    per_page: perPage.value,
                },
            });
            console.log('response.data', response.data);
            students.value = response.data.students.data;

            page.value = response.data.students.current_page;
            total.value = response.data.students.total;
            totalPages.value = response.data.students.last_page;

            summary.value = response.data.summary;
        } finally {
            loading.value = false;
        }
    }

    function search() {
        page.value = 1;

        loadStudents();
    }

    function clearFilters() {
        filters.value = {
            search: '',
            period_id: null,
            status: null,
            invitation_status: null,
        };

        page.value = 1;

        loadStudents();
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

        loadStudents();
    }

    async function exportExcel() {
        const response = await axios.get(
            '/reports/students/export',
            {
                params: {
                    ...filters.value,
                },
                responseType: 'blob',
            },
        );

        const blob = new Blob([response.data], {
            type: response.headers['content-type'],
        });

        const url = window.URL.createObjectURL(blob);

        const link = document.createElement('a');

        link.href = url;
        link.download = 'relatorio-estudantes.xlsx';

        document.body.appendChild(link);

        link.click();

        link.remove();

        window.URL.revokeObjectURL(url);
    }

    loadStudents();

    return {
        students,
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

        loadStudents,

        search,
        clearFilters,
        goToPage,
        exportExcel,
    };
}