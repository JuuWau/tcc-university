<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsPerformanceReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function map($student): array
    {
        $period = $student->currentPeriod?->period;

        return [
            $student->person?->name,

            $student->registration,

            $student->clinic_name,

            $period
                ? "{$period->academic_year}º ano " .
                "{$period->semester}º semestre de " .
                $period->calendar_year
                : null,

            $student->total_appointments ?? 0,

            $student->created_at?->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Nome',
            'RA',
            'Clínica',
            'Período',
            'Atendidos',
            'Cadastro',
        ];
    }
}
