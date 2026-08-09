<?php

namespace App\Data\SchedulesEnrollment;

use App\Http\Requests\OpenClinicsSchedulesEnrollmentRequest;

class OpenClinicsSchedulesEnrollmentFiltersData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
    ) {}

    public static function fromRequest(OpenClinicsSchedulesEnrollmentRequest $request): self 
    {
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 12),
            $request->input('search'),
        );
    }
}