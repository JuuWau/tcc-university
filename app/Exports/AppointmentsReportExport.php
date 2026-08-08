<?php

namespace App\Exports;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppointmentsReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function map($appointment): array
    {
        return [
            $appointment->patient?->code,

            $appointment->patient?->name,

            $appointment->student?->user?->person?->name,

            $appointment->slot?->responsibles
                ->map(fn ($user) => $user->person?->name)
                ->filter()
                ->join(', '),

            $appointment->procedure?->name,

            $appointment->slot?->clinic?->name,

            $appointment->slot?->period
                ? "{$appointment->slot->period->academic_year}º ano " .
                  "{$appointment->slot->period->semester}º semestre de " .
                  $appointment->slot->period->calendar_year
                : null,

            $appointment->scheduled_start_at?->format('d/m/Y'),

            $appointment->scheduled_start_at?->format('H:i')
                . ' - ' .
                $appointment->scheduled_end_at?->format('H:i'),

            match ($appointment->status) {
                'scheduled' => 'Agendado',
                'confirmed' => 'Confirmado',
                'completed' => 'Concluído',
                'canceled' => 'Cancelado',
                'no_show' => 'Não compareceu',
                'rescheduled' => 'Remarcado',
                default => $appointment->status,
            },
        ];
    }

    public function headings(): array
    {
        return [
            'Código',
            'Paciente',
            'Aluno',
            'Responsável',
            'Procedimento',
            'Clínica',
            'Período',
            'Data',
            'Horário',
            'Status',
        ];
    }
}