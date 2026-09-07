<?php

namespace App\Data\Periods;

use App\Http\Requests\TablePeriodRequest;

class PeriodTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly ?string $search,
                public readonly ?int $universityId,
        ) {}

        public static function fromRequest(TablePeriodRequest $request): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 15),
                        $request->input('sort_field', 'calendar_year'),
                        $request->input('sort_dir', 'desc'),
                        $request->input('search'),
                        $request->user()?->university_id,
                );
        }
}
