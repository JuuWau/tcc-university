<?php

namespace App\Data\ScheduleSlot;

use App\Http\Requests\TableOpenClinicSchedulesRequest;

class OpenClinicSchedulesTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly ?int $periodId,
                public readonly ?string $date,
                public readonly int $universityId,
                public readonly int $clinicId
        ) {}

        public static function fromRequest(TableOpenClinicSchedulesRequest $request, int $clinicId): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 10),
                        $request->input('sort_field', 'date'),
                        $request->input('sort_dir', 'asc'),
                        $request->integer('period_id') ?: null,
                        $request->input('date'),
                        $request->user()?->university_id,
                        $clinicId,
                );
        }
}
