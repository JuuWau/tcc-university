<?php

namespace App\Data\OpenClinicsManagement;

use App\Http\Requests\OpenClinicsManagementRequest;

class OpenClinicsManagementFiltersData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
        public readonly int $universityId,
    ) {}

    public static function fromRequest(OpenClinicsManagementRequest $request): self 
    {
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 12),
            $request->input('search'),
            auth()->user()->university_id,
        );
    }
}