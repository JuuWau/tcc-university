<?php

namespace App\Http\Controllers;

use App\Data\StudentsReport\StudentsReportTableFiltersData;
use App\Exports\StudentsReportExport;
use App\Http\Requests\StudentsReportTableRequest;
use App\Services\StudentsReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class StudentsReportController extends Controller
{
    public function __construct(
        protected StudentsReportService $studentService,
    ) {}

    public function index()
    {
        $universityId = request()->user()?->university_id;

        return Inertia::render('reports/students/StudentsReportIndex', [
            'filters' => $this->studentService->filters($universityId),
        ]);
    }

    public function data(StudentsReportTableRequest $request)
    {
        return response()->json(
            $this->studentService->paginate(
                StudentsReportTableFiltersData::fromRequest($request)
            )
        );
    }

    public function exportExcel(StudentsReportTableRequest $request)
    {
        $filters = StudentsReportTableFiltersData::fromRequest($request);

        $query = $this->studentService->studentsForExport($filters);

        return Excel::download(
            new StudentsReportExport($query),
            'relatorio-estudantes.xlsx'
        );
    }
}
