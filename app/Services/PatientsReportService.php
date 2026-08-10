<?php

namespace App\Services;

use App\Data\PatientsReport\PatientsReportTableFiltersData;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PatientsReportService
{
    public function filters(?int $universityId): array
    {
        return [
            'patient_types' => [
                [
                    'id' => 'pediatria',
                    'name' => 'Pediatria',
                ],
                [
                    'id' => 'adulto',
                    'name' => 'Adulto',
                ],
            ],
            'statuses' => [
                [
                    'id' => Patient::STATUS_ATIVO,
                    'name' => 'Ativo',
                ],
                [
                    'id' => Patient::STATUS_INATIVO,
                    'name' => 'Inativo',
                ],
                [
                    'id' => Patient::STATUS_TRATAMENTO,
                    'name' => 'Tratamento',
                ],
                [
                    'id' => Patient::STATUS_PAUSA_TRATAMENTO,
                    'name' => 'Pausa no Tratamento',
                ],
                [
                    'id' => Patient::STATUS_ABANDONO,
                    'name' => 'Abandono',
                ],
                [
                    'id' => Patient::STATUS_CONCLUIDO,
                    'name' => 'Concluído',
                ],
                [
                    'id' => Patient::STATUS_TRANSFERENCIA,
                    'name' => 'Transferência',
                ],
            ],
        ];
    }

    public function paginate(PatientsReportTableFiltersData $filters): array 
    {
        $query = $this->baseQuery($filters);

        $this->applyFilters($query, $filters);

        $summary = $this->summary($filters);

        $patients = $query
            ->orderBy(
                $filters->sortField,
                $filters->sortDir
            )
            ->paginate(
                $filters->perPage,
                ['*'],
                'page',
                $filters->page
            );

        return [
            'patients' => $patients,
            'summary' => $summary,
        ];
    }

    public function patientsForExport(PatientsReportTableFiltersData $filters): Builder 
    {
        $query = $this->baseQuery($filters);

        $this->applyFilters($query, $filters);

        return $query->orderBy(
            $filters->sortField,
            $filters->sortDir
        );
    }

    private function baseQuery(PatientsReportTableFiltersData $filters): Builder 
    {
        return Patient::query()
            ->where(
                'university_id',
                $filters->universityId
            );
    }

    private function applyFilters(Builder $query, PatientsReportTableFiltersData $filters): void 
    {
        if ($filters->search) {
            $search = $filters->search;

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('cpf', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($filters->patientType) {
            $query->where(
                'patient_type',
                $filters->patientType
            );
        }

        if ($filters->status) {
            $query->where(
                'status',
                $filters->status
            );
        }
    }

    private function summary(PatientsReportTableFiltersData $filters): array 
    {
        $query = Patient::query()
            ->where(
                'university_id',
                $filters->universityId
            );

        $this->applyFilters($query, $filters);

        return [
            'total' => (clone $query)->count(),

            'ativo' => (clone $query)
                ->where('status', Patient::STATUS_ATIVO)
                ->count(),

            'inativo' => (clone $query)
                ->where('status', Patient::STATUS_INATIVO)
                ->count(),

            'tratamento' => (clone $query)
                ->where('status', Patient::STATUS_TRATAMENTO)
                ->count(),

            'pausa_tratamento' => (clone $query)
                ->where(
                    'status',
                    Patient::STATUS_PAUSA_TRATAMENTO
                )
                ->count(),

            'abandono' => (clone $query)
                ->where('status', Patient::STATUS_ABANDONO)
                ->count(),

            'concluido' => (clone $query)
                ->where('status', Patient::STATUS_CONCLUIDO)
                ->count(),

            'transferencia' => (clone $query)
                ->where(
                    'status',
                    Patient::STATUS_TRANSFERENCIA
                )
                ->count(),
        ];
    }
}