<?php

namespace App\Data\PatientsReport;

use App\Http\Requests\PatientsReportTableRequest;

class PatientsReportTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly ?string $search,
                public readonly ?string $patientType,
                public readonly ?string $status,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly int $universityId,
        ) {}

        public static function fromRequest(PatientsReportTableRequest $request): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 10),
                        $request->input('search'),
                        $request->input('patient_type'),
                        $request->input('status'),
                        $request->input('sort_field', 'name'),
                        $request->input('sort_dir', 'asc'),
                        auth()->user()->university_id,
                );
        }
}
