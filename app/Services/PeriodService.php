<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Clinic;
use App\Models\Period;
use App\Models\PeriodSpecialty;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

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
                return DB::transaction(function () use ($period, $data) {
                        $period->fill([
                                'academic_year' => $data['academic_year'],
                                'semester' => $data['semester'],
                                'calendar_year' => $data['calendar_year'],
                        ]);

                        $changes = ActivityLogService::getChanges($period);

                        if (isset($data['specialties'])) {
                                ActivityLogService::trackRelationChanges(
                                        $changes,
                                        'specialties',
                                        $period->specialties()->orderBy('name')->pluck('specialties.name')->toArray(),
                                        ActivityLogService::getRelationValues(
                                                Specialty::class,
                                                $data['specialties'],
                                        ),
                                );
                        }

                        $period->save();

                        if (isset($data['specialties'])) {
                                $period->specialties()->sync($data['specialties']);
                        }

                        if (!empty($changes)) {
                                ActivityLogService::updated(
                                        ActivityModules::PERIODS,
                                        "Atualizou o período {$period->academic_year}º ano - {$period->semester}º semestre.",
                                        $period,
                                        $changes,
                                );
                        }

                        return $period->load('specialties');
                });
        }

        public function create(array $data, int $universityId): Period
        {
                if (!$universityId) {
                        throw new \RuntimeException('Salvamento inválido');
                }

                return DB::transaction(function () use ($data, $universityId) {
                        $period = Period::create([
                                'academic_year' => $data['academic_year'],
                                'semester' => $data['semester'],
                                'calendar_year' => $data['calendar_year'],
                                'university_id' => $universityId,
                        ]);

                        $changes = [];

                        if (!empty($data['specialties'])) {
                                $period->specialties()->sync($data['specialties']);

                                ActivityLogService::trackRelationChanges(
                                        $changes,
                                        'specialties',
                                        [],
                                        ActivityLogService::getRelationValues(
                                                Specialty::class,
                                                $data['specialties'],
                                        ),
                                );
                        }

                        ActivityLogService::created(
                                ActivityModules::PERIODS,
                                "Cadastrou o período {$period->academic_year}º ano - {$period->semester}º semestre.",
                                $period,
                                $changes,
                        );

                        return $period->load('specialties');
                });
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
                
                DB::transaction(function () use ($period) {
                        $changes = [];

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                'specialties',
                                ActivityLogService::getModelRelationValues(
                                        $period,
                                        'specialties',
                                ),
                                [],
                        );

                        ActivityLogService::deleted(
                                ActivityModules::PERIODS,
                                "Removeu o período {$period->academic_year}º ano - {$period->semester}º semestre.",
                                $period,
                                $changes,
                        );

                        PeriodSpecialty::where('period_id', $period->id)
                                ->update(['deleted_at' => now()]);

                        $period->delete();
                });
        }

        public function getPeriods(?int $universityId)
        {
                return Period::query()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->where('calendar_year', now()->year)
                        ->orderByDesc('calendar_year')
                        ->orderBy('academic_year')
                        ->orderBy('semester')
                        ->get(['id', 'academic_year', 'semester', 'calendar_year'])
                        ->map(fn($period) => [
                                'id' => $period->id,
                                'label' => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                        ]);
        }

        public function getPeriodsByClinic(Clinic $clinic, User $user)
        {
                return Period::query()
                        ->where('calendar_year', now()->year)
                        ->whereHas('scheduleSlots', function ($query) use ($clinic, $user) {
                                $query->where('clinic_id', $clinic->id);

                                if (! $user->hasRole('admin')) {
                                        $query->whereHas('responsibles', function ($query) use ($user) {
                                                $query->where('users.id', $user->id);
                                        });
                                }
                        })
                        ->orderByDesc('calendar_year')
                        ->orderBy('academic_year')
                        ->orderBy('semester')
                        ->get([
                                'id',
                                'academic_year',
                                'semester',
                                'calendar_year',
                        ])
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
