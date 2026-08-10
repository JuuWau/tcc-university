<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsReportExport implements FromQuery, WithHeadings, WithMapping
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
        $invite = $student->user?->invite;

        return [
            $student->person?->name,

            $student->registration,

            $period
                ? "{$period->academic_year}º ano " .
                    "{$period->semester}º semestre de " .
                    $period->calendar_year
                : null,

            $student->deleted_at
                ? 'Inativo'
                : 'Ativo',

            match (true) {
                !$invite => 'Sem convite',
                $invite->used_at !== null => 'Aceito',
                default => 'Pendente',
            },

            $student->created_at?->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Nome',
            'RA',
            'Período',
            'Status',
            'Convite',
            'Cadastro',
        ];
    }
}