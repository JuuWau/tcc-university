<?php

namespace App\Services;

use App\Models\Period;
use App\Models\PeriodSpecialty;
use App\Models\User;

class PeriodService
{
        /**
         * Return all periods from a university
         */
        public function all(int $universityId)
        {
                return Period::orderBy('calendar_year')->with('specialties')
                        ->where('university_id', $universityId)
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
                if ($period->scheduleSlots()->exists()) {
                        throw new \DomainException(
                        'Não é possível excluir este período pois ele possui horários vinculados.'
                        );
                }

                if ($period->studentPeriods()->exists()) {
                        throw new \DomainException(
                        'Não é possível excluir este período pois ele possui alunos vinculados.'
                        );
                }

                PeriodSpecialty::where('period_id', $period->id)
                        ->update(['deleted_at' => now()]);

                $period->delete();
        }

        public function getPeriods(?int $universityId)
        {
                return Period::query()
                ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                ->orderByDesc('calendar_year')
                ->orderBy('academic_year')
                ->orderBy('semester')
                ->get(['id', 'academic_year', 'semester', 'calendar_year'])
                ->map(fn($period) => [
                        'id' => $period->id,
                        'label' => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                ]);
        }

        public function getIdByUserId(int $userId): ?int
        {
                return Period::query()
                        ->whereHas('studentPeriods.student', fn($q) => $q->where('user_id', $userId))
                        ->whereHas('studentPeriods', fn($q) => $q->where('is_current', true))
                        ->value('id');
        }
}
