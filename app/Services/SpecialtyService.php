<?php

namespace App\Services;

use App\Models\Specialty;

class SpecialtyService
{
        /**
         * Return all specialties from a university
         */
        public function all()
        {
                return Specialty::orderBy('name')->get();
        }

        public function update(Specialty $specialty, array $data): Specialty
        {
                $specialty->update([
                        'name' => $data['name'],
                ]);

                return $specialty;
        }

        public function create(array $data, int $universityId): Specialty
        {
                if (!$universityId) {
                        throw new \RuntimeException('Salvamento Inválido');
                }

                return Specialty::create([
                        'name' => $data['name'],
                        'university_id' => $universityId,
                ]);
        }

        public function delete(Specialty $specialty): void
        {
                $specialty->delete();
        }
}
