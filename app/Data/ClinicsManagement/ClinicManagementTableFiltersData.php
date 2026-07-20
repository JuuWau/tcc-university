<?php
namespace App\Data\ClinicsManagement;

use App\Http\Requests\ClinicManagementTableRequest;
use App\Http\Requests\TableUserRequest;

class ClinicManagementTableFiltersData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $status,
        public readonly ?string $search,
        public readonly string $sortField,
        public readonly string $sortDir,
        public readonly int $universityId,
    ) {}

    public static function fromRequest(ClinicManagementTableRequest $request): self 
    {
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 10),
            $request->input('status', 'enrolled'),
            $request->input('search'),
            $request->input('sort_field', 'created_at'),
            $request->input('sort_dir', 'desc'),
            auth()->user()->university_id,
        );
    }
}