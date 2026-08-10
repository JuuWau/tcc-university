<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PatientsReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function map($patient): array
    {
        return [
            $patient->name,
            $patient->code,
            $patient->cpf,
            $patient->phone,
            $patient->birth_date
                ? Carbon::parse($patient->birth_date)->format('d/m/Y')
                : null,

            match ($patient->patient_type) {
                'pediatria' => 'Pediatria',
                'adulto' => 'Adulto',
                default => $patient->patient_type,
            },

            match ($patient->status) {
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
                'tratamento' => 'Tratamento',
                'pausa_tratamento' => 'Pausa no Tratamento',
                'abandono' => 'Abandono',
                'concluido' => 'Concluído',
                'transferencia' => 'Transferência',
                default => $patient->status,
            },

            $patient->created_at
                ? Carbon::parse($patient->created_at)->format('d/m/Y')
                : null,
        ];
    }

    public function headings(): array
    {
        return [
            'Nome',
            'Código',
            'CPF',
            'Telefone',
            'Data de nascimento',
            'Tipo',
            'Status',
            'Cadastro',
        ];
    }
}