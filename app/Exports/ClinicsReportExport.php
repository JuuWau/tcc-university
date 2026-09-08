<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClinicsReportExport implements FromQuery, WithHeadings, WithMapping
{
        public function __construct(
                private Builder $query
        ) {}

        public function query(): Builder
        {
                return $this->query;
        }

        public function map($clinic): array
        {
                return [
                        $clinic->name,
                        $clinic->period_name,
                        $clinic->slots_count,
                        $clinic->total_available_slots,
                        $clinic->used_slots,
                        $clinic->remaining_slots,
                        $clinic->occupancy_rate !== null
                                ? number_format(
                                        $clinic->occupancy_rate,
                                        2,
                                        ',',
                                        '.'
                                ) . '%'
                                : null,

                        $clinic->active
                                ? 'Ativa'
                                : 'Inativa',
                ];
        }

        public function headings(): array
        {
                return [
                        'Clínica',
                        'Período',
                        'Slots',
                        'Vagas disponibilizadas',
                        'Atendimentos',
                        'Vagas restantes',
                        'Taxa de ocupação',
                        'Status',
                ];
        }
}
