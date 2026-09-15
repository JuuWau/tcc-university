<?php

namespace App\Data\StudentsPerformanceReport;

use App\Http\Requests\StudentsPerformanceReportTableRequest;

class StudentsPerformanceReportTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly ?string $search,
                public readonly ?int $periodId,
                public readonly ?string $clinicId,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly int $universityId,
        ) {}

        public static function fromRequest(StudentsPerformanceReportTableRequest $request): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 10),
                        $request->input('search'),
                        $request->integer('period_id') ?: null,
                        $request->integer('clinic_id') ?: null,
                        $request->input('sort_field', 'created_at'),
                        $request->input('sort_dir', 'desc'),
                        auth()->user()->university_id,
                );
        }
}
