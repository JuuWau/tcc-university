<?php

namespace App\Data\ClinicsReport;

use App\Http\Requests\ClinicsReportTableRequest;

class ClinicsReportTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly ?string $search,
                public readonly ?int $periodId,
                public readonly ?string $status,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly int $universityId,
        ) {}

        public static function fromRequest(ClinicsReportTableRequest $request): self 
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 10),
                        $request->input('search'),
                        $request->integer('period_id') ?: null,
                        $request->input('status'),
                        $request->input('sort_field', 'created_at'),
                        $request->input('sort_dir', 'desc'),
                        auth()->user()->university_id,
                );
        }
}
