import axios from 'axios';
import { onMounted, ref } from 'vue';
import type { AppointmentsFilters } from '@/types/appointments-report/appointmentsFilters';
import type { Appointment } from '@/types/appointments-report/appointment';
import type { AppointmentsSummary } from '@/types/appointments-report/appointmentsSummary';
import { usePage } from '@inertiajs/vue3';
import { appointmentsReportFiltersSchema } from '@/schemas/appointmentsReportFiltersSchema';
import { toast } from 'vue3-toastify';

export function useAppointments() {
    const page = ref(1);
    const perPage = ref(10);
    const total = ref(0);
    const totalPages = ref(0);
    const loading = ref(false);
    const pageData = usePage();
    const clinics = ref(pageData.props.filters.clinics);
    const responsibles = ref(pageData.props.filters.responsibles);
    const periods = ref(pageData.props.filters.periods);
    const filterErrors = ref<Record<string, string>>({});
    const currentMonth = getCurrentMonthDates();

    const filters = ref<AppointmentsFilters>({
        clinic_id: null,
        responsible_id: null,
        student_id: null,
        period_id: null,
        search: '',
        status: null,
        start_date: currentMonth.start_date,
        end_date: currentMonth.end_date,
    }); 

    const appointments = ref<Appointment[]>([]);

    const summary = ref<AppointmentsSummary>({
        total: 0,
        scheduled: 0,
        confirmed: 0,
        completed: 0,
        canceled: 0,
        no_show: 0,
        rescheduled: 0,
    });

    async function search(resetPage = true) {
        if (!validateFilters()) {
            return;
        }

        loading.value = true;

        if (resetPage) {
            page.value = 1;
        }

        try {
            const { data } = await axios.get(
                '/reports/appointments/data',
                {
                    params: {
                        page: page.value,
                        per_page: perPage.value,
                        ...getFilterParams(),
                    },
                },
            );

            appointments.value = data.appointments.data;
            total.value = data.appointments.total;
            totalPages.value = data.appointments.last_page;
            summary.value = data.summary;
        } finally {
            loading.value = false;
        }
    }

    function validateFilters(): boolean {
        const result = appointmentsReportFiltersSchema.safeParse(
            filters.value,
        );

        if (result.success) {
            return true;
        }

        const firstError = result.error.issues[0];

        if (firstError) {
            toast.error(firstError.message);
        }

        const hasPeriodError = result.error.issues.some(
            (issue) =>
                issue.path.includes('end_date') &&
                issue.message === 'O período máximo para consulta é de 12 meses.',
        );

        if (hasPeriodError) {
            resetDateRange();
        }

        return false;
    }

    function goToPage(pageNumber: number) {
        page.value = pageNumber;
        search(false);
    }

    function getCurrentMonthDates() {
        const now = new Date();

        const firstDay = new Date(
            now.getFullYear(),
            now.getMonth(),
            1,
        );

        const lastDay = new Date(
            now.getFullYear(),
            now.getMonth() + 1,
            0,
        );

        const formatDate = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        };

        return {
            start_date: formatDate(firstDay),
            end_date: formatDate(lastDay),
        };
    }

    function getFilterParams() {
        return {
            search: filters.value.search,
            clinic_id: filters.value.clinic_id,
            responsible_id: filters.value.responsible_id,
            period_id: filters.value.period_id,
            start_date: filters.value.start_date,
            end_date: filters.value.end_date,
            status: filters.value.status,
        };
    }

    async function exportExcel() {
        if (!validateFilters()) {
            return;
        }

        loading.value = true;

        try {
            const response = await axios.get(
                '/reports/appointments/export-excel',
                {
                    params: getFilterParams(),
                    responseType: 'blob',
                },
            );

            const url = window.URL.createObjectURL(
                new Blob([response.data]),
            );

            const link = document.createElement('a');

            link.href = url;
            link.download = 'relatorio-agendamentos.xlsx';

            document.body.appendChild(link);
            link.click();

            link.remove();

            window.URL.revokeObjectURL(url);
        } finally {
            loading.value = false;
        }
    }

    function resetDateRange() {
        const currentMonth = getCurrentMonthDates();

        filters.value.start_date = currentMonth.start_date;
        filters.value.end_date = currentMonth.end_date;
    }

    async function exportPdf() {
        loading.value = true;

        try {
            const response = await axios.get('/reports/appointments/export/pdf', {
                params: {
                    search: filters.value.search,
                    clinic_id: filters.value.clinic_id,
                    responsible_id: filters.value.responsible_id,
                    period_id: filters.value.period_id,
                    start_date: filters.value.start_date,
                    end_date: filters.value.end_date,
                    status: filters.value.status,
                },
                responseType: 'blob',
            });

            const blob = new Blob(
                [response.data],
                {
                    type: 'application/pdf',
                },
            );

            const url = window.URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = 'relatorio-agendamentos.pdf';

            document.body.appendChild(link);
            link.click();
            link.remove();

            window.URL.revokeObjectURL(url);
        } finally {
            loading.value = false;
        }
    }

    function clearFilters() {
        const currentMonth = getCurrentMonthDates();

        filters.value = {
            clinic_id: null,
            responsible_id: null,
            student_id: null,
            period_id: null,
            patient: '',
            status: null,
            start_date: currentMonth.start_date,
            end_date: currentMonth.end_date,
        };

        page.value = 1;

        search();
    }

    onMounted(() => {
        search();
    });

    return {
        loading,
        filters,
        total,
        totalPages,
        page,
        perPage,
        appointments,
        summary,
        clinics,
        responsibles,
        periods,
        search,
        clearFilters,
        exportPdf,
        exportExcel,
        goToPage,
    };
}