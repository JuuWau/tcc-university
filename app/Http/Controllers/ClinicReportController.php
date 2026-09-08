<?php

namespace App\Http\Controllers;

use App\Data\ClinicsReport\ClinicsReportTableFiltersData;
use App\Exports\ClinicsReportExport;
use App\Http\Requests\ClinicsReportTableRequest;
use App\Services\ClinicsReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ClinicReportController extends Controller
{
    public function __construct(
        protected ClinicsReportService $clinicService,
    ) {}

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('reports/clinics/ClinicsReportIndex', [
            'filters' => $this->clinicService->filters($universityId),
        ]);
    }

    public function data(ClinicsReportTableRequest $request)
    {
        return response()->json(
            $this->clinicService->paginate(
                ClinicsReportTableFiltersData::fromRequest($request)
            )
        );
    }

    public function exportExcel(ClinicsReportTableRequest $request)
    {
        $filters = ClinicsReportTableFiltersData::fromRequest($request);

        $query = $this->clinicService->clinicsForExport($filters);

        return Excel::download(
            new ClinicsReportExport($query),
            'relatorio-clinicas.xlsx'
        );
    }
}
