<?php

namespace App\Http\Controllers;

use App\Data\AppointmentReport\AppointmentsReportTableFiltersData;
use App\Exports\AppointmentsReportExport;
use App\Http\Requests\AppointmentsReportTableRequest;
use App\Services\AppointmentReportsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentReportsController extends Controller
{

    public function __construct(
        protected AppointmentReportsService $appointmentService,
    ) {}

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('reports/appointments/AppointmentsReportIndex', [
            'filters' => $this->appointmentService->filters($universityId),
        ]);
    }

    public function data(AppointmentsReportTableRequest $request)
    {
        return response()->json(
            $this->appointmentService->paginate(
                AppointmentsReportTableFiltersData::fromRequest($request)
            )
        );
    }

    public function exportExcel(AppointmentsReportTableRequest $request)
    {
        $filters = AppointmentsReportTableFiltersData::fromRequest($request);

        $query = $this->appointmentService->appointmentsForExport($filters);

        return Excel::download(
            new AppointmentsReportExport($query),
            'relatorio-agendamentos.xlsx'
        );
    }
}
