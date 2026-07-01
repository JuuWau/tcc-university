<?php

namespace App\Services;

use App\Models\Procedure;

class ProcedureService
{
    public function all(int $universityId)
    {
        return Procedure::orderBy('name')
            ->where('university_id', $universityId)
            ->with('specialty:id,name')
            ->get();
    }

    public function create(array $data, int $universityId): Procedure
    {
        if (!$universityId) {
            throw new \RuntimeException('Salvamento inválido');
        }

        $procedure = Procedure::create([
            'name' => $data['name'],
            'specialty_id' => $data['specialty_id'],
            'university_id' => $universityId,
        ]);

        return $procedure->load('specialty:id,name');
    }

    public function update(Procedure $procedure, array $data): Procedure
    {
        $procedure->update([
            'name' => $data['name'],
            'specialty_id' => $data['specialty_id'],
        ]);

        return $procedure->load('specialty');
    }

    public function delete(Procedure $procedure): void
    {
        $procedure->delete();
    }
}
