<?php

namespace App\Http\Controllers;

use App\Data\StudentsPerformanceReport\StudentsPerformanceReportTableFiltersData;
use App\Http\Requests\StudentsPerformanceReportTableRequest;
use App\Exports\StudentsPerformanceReportExport;
use App\Services\StudentsPerformanceReportService;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class StudentsPerformanceReportController extends Controller
{
        public function __construct(
        protected StudentsPerformanceReportService $studentPerformanceService,
    ) {}

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('reports/students-performance/StudentsPerformanceReportIndex', [
            'filters' => $this->studentPerformanceService->filters($universityId),
        ]);
    }

    public function data(StudentsPerformanceReportTableRequest $request)
    {
        return response()->json(
            $this->studentPerformanceService->paginate(
                StudentsPerformanceReportTableFiltersData::fromRequest($request)
            )
        );
    }

    public function exportExcel(StudentsPerformanceReportTableRequest $request)
    {
        $filters = StudentsPerformanceReportTableFiltersData::fromRequest($request);

        $query = $this->studentPerformanceService->studentsForExport($filters);

        return Excel::download(
            new StudentsPerformanceReportExport($query),
            'relatorio-performance-clinica-estudantes.xlsx'
        );
    }
}
