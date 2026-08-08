<?php

namespace App\Data\AppointmentReport;

use App\Http\Requests\AppointmentsReportTableRequest;

class AppointmentsReportTableFiltersData
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
        public readonly ?int $clinicId,
        public readonly ?int $responsibleId,
        public readonly ?int $studentId,
        public readonly ?int $patientId,
        public readonly ?int $periodId,
        public readonly ?string $status,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly string $sortField,
        public readonly string $sortDir,
        public readonly int $universityId,
    ) {}

    public static function fromRequest(AppointmentsReportTableRequest $request): self
    {   
        return new self(
            $request->integer('page', 1),
            $request->integer('per_page', 10),
            $request->input('search'),
            $request->integer('clinic_id') ?: null,
            $request->integer('responsible_id') ?: null,
            $request->integer('student_id') ?: null,
            $request->integer('patient_id') ?: null,
            $request->integer('period_id') ?: null,
            $request->input('status'),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('sort_field', 'scheduled_start_at'),
            $request->input('sort_dir', 'desc'),
            auth()->user()->university_id,
        );
    }
}