<?php

namespace App\Services;

use App\Models\Period;
use App\Models\PeriodSpecialty;

class PeriodService
{
        /**
         * Return all periods from a university
         */
        public function all()
        {
                return Period::orderBy('calendar_year')->with('specialties')
                        ->get();
        }

        public function update(Period $period, array $data): Period
        {
                $period->update([
                        'academic_year' => $data['academic_year'],
                        'semester' => $data['semester'],
                        'calendar_year' => $data['calendar_year'],
                ]);

                if (isset($data['specialties'])) {
                        $period->specialties()->sync($data['specialties']);
                }

                return $period->load('specialties');
        }

        public function create(array $data, int $universityId): Period
        {
                if (!$universityId) {
                        throw new \RuntimeException('Salvamento inválido');
                }

                $period = Period::create([
                        'academic_year' => $data['academic_year'],
                        'semester' => $data['semester'],
                        'calendar_year' => $data['calendar_year'],
                        'university_id' => $universityId,
                ]);

                if (!empty($data['specialties'])) {
                        $period->specialties()->sync($data['specialties']);
                }

                return $period->load('specialties');
        }

        public function delete(Period $period): void
        {
                PeriodSpecialty::where('period_id', $period->id)
                        ->update(['deleted_at' => now()]);

                $period->delete();
        }
}
