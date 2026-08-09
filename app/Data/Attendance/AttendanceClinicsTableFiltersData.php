<?php

namespace App\Data\Attendance;

use App\Http\Requests\AttendanceClinicsTableRequest;

class AttendanceClinicsTableFiltersData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
    ) {}

    public static function fromRequest(
        AttendanceClinicsTableRequest $request
    ): self {
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 12),
            $request->input('search'),
        );
    }
}