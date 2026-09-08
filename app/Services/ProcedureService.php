<?php

namespace App\Services;

use App\Constants\ActivityLogPrefixes;
use App\Constants\ActivityModules;
use App\Data\Procedures\ProcedureTableFiltersData;
use App\Models\Clinic;
use App\Models\Procedure;
use App\Models\Specialty;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProcedureService
{
    public function paginate(ProcedureTableFiltersData $filters): LengthAwarePaginator
    {
        return Procedure::query()
            ->with('specialty:id,name')
            ->where('university_id', $filters->universityId)
            ->whereNull('procedures.deleted_at')
            ->when($filters->search, function ($query) use ($filters) {
                $search = $filters->search;

                $query->where(function ($q) use ($search) {
                    $q->where('procedures.name', 'like', "%{$search}%")
                        ->orWhereHas(
                            'specialty',
                            fn($specialty) =>
                            $specialty->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->orderBy($filters->sortField, $filters->sortDir)
            ->orderBy('id', $filters->sortDir)
            ->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }

    public function all(?int $universityId, ?int $clinicId = null)
    {
        return Procedure::query()
            ->where('university_id', $universityId)
            ->when($clinicId, function ($query) use ($clinicId, $universityId) {
                $query->whereIn(
                    'specialty_id',
                    Clinic::query()
                        ->where('id', $clinicId)
                        ->where('university_id', $universityId)
                        ->select('specialty_id')
                );
            })
            ->with('specialty')
            ->get();
    }

    public function create(array $data, int $universityId): Procedure
    {
        if (!$universityId) {
            throw new \RuntimeException('Salvamento inválido');
        }

        return DB::transaction(function () use ($data, $universityId) {
            $procedure = Procedure::create([
                'name' => $data['name'],
                'specialty_id' => $data['specialty_id'],
                'university_id' => $universityId,
            ]);

            $changes = [];

            ActivityLogService::trackRelationChanges(
                $changes,
                ActivityLogPrefixes::SPECIALTY,
                [],
                ActivityLogService::getRelationValues(
                    Specialty::class,
                    [$data['specialty_id']],
                ),
            );

            ActivityLogService::created(
                ActivityModules::PROCEDURES,
                "Cadastrou o procedimento '{$procedure->name}'.",
                $procedure,
                $changes,
            );

            return $procedure->load('specialty:id,name');
        });
    }

    public function update(Procedure $procedure, array $data): Procedure
    {
        return DB::transaction(function () use ($procedure, $data) {
            $procedure->fill([
                'name' => $data['name'],
                'specialty_id' => $data['specialty_id'],
            ]);

            $changes = ActivityLogService::getChanges($procedure);

            ActivityLogService::trackBelongsToChange(
                $changes,
                'specialty_id',
                ActivityLogPrefixes::SPECIALTY,
                Specialty::class,
                null,
                $data['specialty_id'],
            );

            $procedure->save();

            if (!empty($changes)) {
                ActivityLogService::updated(
                    ActivityModules::PROCEDURES,
                    "Atualizou o procedimento '{$procedure->name}'.",
                    $procedure,
                    $changes,
                );
            }

            return $procedure->load('specialty');
        });
    }

    public function delete(Procedure $procedure): void
    {
        DB::transaction(function () use ($procedure) {
            $changes = [];

            ActivityLogService::trackBelongsToChange(
                $changes,
                'specialty_id',
                ActivityLogPrefixes::SPECIALTY,
                Specialty::class,
                $procedure->specialty_id,
                null,
            );

            ActivityLogService::deleted(
                ActivityModules::PROCEDURES,
                "Removeu o procedimento '{$procedure->name}'.",
                $procedure,
                $changes,
            );

            $procedure->delete();
        });
    }
}
