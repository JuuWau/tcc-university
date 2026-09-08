<?php

namespace App\Data\Specialty;

use App\Http\Requests\TableSpecialtyRequest;

class SpecialtyTableFiltersData
{
        public function __construct(
                public readonly int $page,
                public readonly int $perPage,
                public readonly string $sortField,
                public readonly string $sortDir,
                public readonly ?string $search,
                public readonly ?int $universityId,
        ) {}

        public static function fromRequest(TableSpecialtyRequest $request): self
        {
                return new self(
                        $request->integer('page', 1),
                        $request->integer('per_page', 15),
                        $request->input('sort_field', 'name'),
                        $request->input('sort_dir', 'asc'),
                        $request->input('search'),
                        $request->user()?->university_id,
                );
        }
}
