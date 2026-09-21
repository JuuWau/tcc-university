<?php

namespace App\Data\PrePatients;

use App\Http\Requests\PrePatientTableRequest;

class PrePatientTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly ?string $search,
                public readonly ?string $status,
                public readonly ?int $clinicId,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly int $universityId,
        ) {}

        public static function fromRequest(PrePatientTableRequest $request): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 10),
                        $request->input('search'),
                        $request->input('status'),
                        $request->integer('clinic_id') ?: null,
                        $request->input('sort_field', 'created_at'),
                        $request->input('sort_dir', 'desc'),
                        auth()->user()->university_id,
                );
        }
}
