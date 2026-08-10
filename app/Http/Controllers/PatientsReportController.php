<?php

namespace App\Http\Controllers;

use App\Data\PatientsReport\PatientsReportTableFiltersData;
use App\Http\Requests\PatientsReportTableRequest;
use App\Services\PatientsReportService;
use Inertia\Inertia;
use App\Exports\PatientsReportExport;
use Maatwebsite\Excel\Facades\Excel;

class PatientsReportController extends Controller
{
    public function __construct(
        protected PatientsReportService $patientsService,
    ) {}

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('reports/patients/PatientsReportIndex', [
            'filters' => $this->patientsService->filters($universityId),
        ]);
    }

    public function data(PatientsReportTableRequest $request)
    {
        return response()->json(
            $this->patientsService->paginate(
                PatientsReportTableFiltersData::fromRequest($request)
            )
        );
    }

    public function exportExcel(PatientsReportTableRequest $request)
    {
        $filters = PatientsReportTableFiltersData::fromRequest($request);

        $query = $this->patientsService->patientsForExport($filters);

        return Excel::download(
            new PatientsReportExport($query),
            'relatorio-pacientes.xlsx'
        );
    }
}
