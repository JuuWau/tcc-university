<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Specialty;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
class SpecialtyService
{
        /**
         * Return all specialties from a university
         */
        public function all(int $universityId)
        {
                return Specialty::orderBy('name')->where('university_id', $universityId)->get();
        }

        public function update(Specialty $specialty, array $data): Specialty
        {
                $specialty->fill([
                        'name' => $data['name'],
                ]);

                $changes = ActivityLogService::getChanges($specialty);

                $specialty->save();

                ActivityLogService::updated(
                        ActivityModules::SPECIALTIES,
                        "Atualizou a especialidade '{$specialty->name}'.",
                        $specialty,
                        $changes,
                );

                return $specialty;
        }

        public function create(array $data, int $universityId): Specialty
        {
                if (!$universityId) {
                        throw new \RuntimeException('Salvamento Inválido');
                }

                $specialty = Specialty::create([
                        'name' => $data['name'],
                        'university_id' => $universityId,
                ]);

                ActivityLogService::created(
                        ActivityModules::SPECIALTIES,
                        "Cadastrou a especialidade '{$specialty->name}'.",
                        $specialty,
                );

                return $specialty;
        }

        public function delete(Specialty $specialty): void
        {
                if ($specialty->procedures()->exists()) {
                        throw new \DomainException(
                        'Não é possível excluir a especialidade pois existem procedimentos vinculados.'
                        );
                }

                if ($specialty->periods()->exists()) {
                        throw new \DomainException(
                        'Não é possível excluir a especialidade pois existem períodos vinculados.'
                        );
                }

                if ($specialty->clinics()->exists()) {
                        throw new \DomainException(
                        'Não é possível excluir a especialidade pois existem clínicas vinculadas.'
                        );
                }

                ActivityLogService::deleted(
                        ActivityModules::SPECIALTIES,
                        "Removeu a especialidade '{$specialty->name}'.",
                        $specialty,
                );

                $specialty->delete();
        }
}
