<?php

namespace App\Data\Patients;

use App\Http\Requests\TablePatientRequest;

class PatientTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly string $status,
                public readonly ?string $search,
                public readonly ?int $universityId,
        ) {}

        public static function fromRequest(TablePatientRequest $request): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 15),
                        $request->input('sort_field', 'created_at'),
                        $request->input('sort_dir', 'desc'),
                        $request->input('status', 'all'),
                        $request->input('search'),
                        $request->user()?->university_id,
                );
        }
}
