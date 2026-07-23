<?php

namespace App\Data\Patients;

use App\Http\Requests\PatientClinicsTableRequest;

class PatientClinicsTableFiltersData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $status,
        public readonly string $sortField,
        public readonly string $sortDir,
        public readonly int $universityId,
    ) {}

    public static function fromRequest(PatientClinicsTableRequest $request): self 
    {
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 10),
            $request->input('status', 'enrolled'),
            $request->input('sort_field', 'created_at'),
            $request->input('sort_dir', 'desc'),
            auth()->user()->university_id,
        );
    }
}