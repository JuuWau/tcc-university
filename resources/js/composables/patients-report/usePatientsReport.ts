import { computed, ref } from 'vue';
import axios from 'axios';
import type { PatientReport, PatientReportFilters, PatientReportSummary, PatientsReportContext, } from '@/types/patients-report/patientsReport';

export function usePatientsReport(initialFilters: PatientReportFilters): PatientsReportContext 
{
    const filters = ref({
        ...initialFilters,
    });

    const patients = ref<PatientReport[]>([]);

    const summary = ref<PatientReportSummary>({
        total: 0,
        ativo: 0,
        inativo: 0,
        tratamento: 0,
        pausa_tratamento: 0,
        abandono: 0,
        concluido: 0,
        transferencia: 0,
    });

    const loading = ref(false);

    const page = ref(1);
    const perPage = ref(15);
    const total = ref(0);
    const totalPages = ref(0);

    const hasActiveFilters = computed(() => {
        return Boolean(
            filters.value.search ||
            filters.value.patient_type ||
            filters.value.status
        );
    });

    const activeFiltersCount = computed(() => {
        let count = 0;

        if (filters.value.search) {
            count++;
        }

        if (filters.value.patient_type) {
            count++;
        }

        if (filters.value.status) {
            count++;
        }

        return count;
    });

    async function load() {
        loading.value = true;

        try {
            const response = await axios.get(
                '/reports/patients/data',
                {
                    params: {
                        ...filters.value,
                        page: page.value,
                        per_page: perPage.value,
                    },
                }
            );

            patients.value = response.data.patients.data;

            summary.value = response.data.summary;

            total.value = response.data.patients.total;

            totalPages.value =
                response.data.patients.last_page;
        } finally {
            loading.value = false;
        }
    }

    function search() {
        page.value = 1;

        load();
    }

    function goToPage(newPage: number) {
        if (
            newPage < 1 ||
            newPage > totalPages.value
        ) {
            return;
        }

        page.value = newPage;

        load();
    }

    function clearFilters() {
        filters.value = {
            ...initialFilters,
            search: null,
            patient_type: null,
            status: null,
        };

        page.value = 1;

        load();
    }

    async function exportExcel() {
        const response = await axios.get(
            '/reports/patients/export',
            {
                params: {
                    ...filters.value,
                },
                responseType: 'blob',
            }
        );

        const blob = new Blob(
            [response.data],
            {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            }
        );

        const url = window.URL.createObjectURL(blob);

        const link = document.createElement('a');

        link.href = url;

        link.download = 'relatorio-pacientes.xlsx';

        document.body.appendChild(link);

        link.click();

        link.remove();

        window.URL.revokeObjectURL(url);
    }

    load();

    return {
        filters,
        patients,
        summary,
        loading,

        page,
        perPage,
        total,
        totalPages,

        hasActiveFilters,
        activeFiltersCount,

        load,
        search,
        goToPage,
        clearFilters,
        exportExcel,
    };
}