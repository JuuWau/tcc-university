<?php

namespace App\Services;

use App\Data\ClinicsReport\ClinicsReportTableFiltersData;
use App\Models\Clinic;
use App\Models\Period;
use App\Models\ScheduleEnrollment;
use Illuminate\Database\Eloquent\Builder;

class ClinicsReportService
{
        public function filters(?int $universityId): array
        {
                return [
                        'periods' => Period::query()
                                ->where('university_id', $universityId)
                                ->orderByDesc('calendar_year')
                                ->orderByDesc('semester')
                                ->orderByDesc('academic_year')
                                ->get([
                                        'id',
                                        'academic_year',
                                        'semester',
                                        'calendar_year',
                                ])
                                ->map(function ($period) {
                                        return [
                                                'id' => $period->id,
                                                'name' => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                                        ];
                                })
                                ->all(),
                ];
        }

        public function paginate(ClinicsReportTableFiltersData $filters): array
        {
                $query = $this->baseQuery($filters);

                $this->applyFilters($query, $filters);

                $clinics = $query
                        ->orderBy(
                                $this->resolveSortField($filters->sortField),
                                $filters->sortDir
                        )
                        ->paginate(
                                $filters->perPage,
                                ['clinics.*'],
                                'page',
                                $filters->page
                        );

                return [
                        'clinics' => $clinics,
                        'summary' => $this->summary($filters),
                ];
        }

        public function clinicsForExport(ClinicsReportTableFiltersData $filters): Builder
        {
                $query = $this->baseQuery($filters);

                $this->applyFilters($query, $filters);

                return $query->orderBy(
                        $this->resolveSortField($filters->sortField),
                        $filters->sortDir
                );
        }

        private function baseQuery(ClinicsReportTableFiltersData $filters): Builder
        {
                return Clinic::query()
                        ->where(
                                'clinics.university_id',
                                $filters->universityId
                        )

                        ->withCount([
                                'scheduleSlots as schedule_slots_count' => function ($query) use ($filters) {
                                        if ($filters->periodId) {
                                                $query->where(
                                                        'period_id',
                                                        $filters->periodId
                                                );
                                        }
                                },

                                'scheduleEnrollments as enrollments_count' => function ($query) use ($filters) {
                                        if ($filters->periodId) {
                                                $query->whereHas('slot', function ($query) use ($filters) {
                                                        $query->where(
                                                                'period_id',
                                                                $filters->periodId
                                                        );
                                                });
                                        }
                                },

                                'scheduleEnrollments as attended_count' => function ($query) use ($filters) {
                                        $query->where(
                                                'status',
                                                ScheduleEnrollment::STATUS_ATTENDED
                                        );

                                        if ($filters->periodId) {
                                                $query->whereHas('slot', function ($query) use ($filters) {
                                                        $query->where(
                                                                'period_id',
                                                                $filters->periodId
                                                        );
                                                });
                                        }
                                },

                                'scheduleEnrollments as missed_count' => function ($query) use ($filters) {
                                        $query->where(
                                                'status',
                                                ScheduleEnrollment::STATUS_MISSED
                                        );

                                        if ($filters->periodId) {
                                                $query->whereHas('slot', function ($query) use ($filters) {
                                                        $query->where(
                                                                'period_id',
                                                                $filters->periodId
                                                        );
                                                });
                                        }
                                },

                                'scheduleEnrollments as canceled_count' => function ($query) use ($filters) {
                                        $query->where(
                                                'status',
                                                ScheduleEnrollment::STATUS_CANCELED
                                        );

                                        if ($filters->periodId) {
                                                $query->whereHas('slot', function ($query) use ($filters) {
                                                        $query->where(
                                                                'period_id',
                                                                $filters->periodId
                                                        );
                                                });
                                        }
                                },
                        ])

                        ->withSum([
                                'scheduleSlots as available_slots_sum' => function ($query) use ($filters) {
                                        if ($filters->periodId) {
                                                $query->where(
                                                        'period_id',
                                                        $filters->periodId
                                                );
                                        }
                                },
                        ], 'available_slots');
        }

        private function applyFilters(Builder $query, ClinicsReportTableFiltersData $filters): void
        {
                if ($filters->search) {
                        $query->where(
                                'clinics.name',
                                'ilike',
                                "%{$filters->search}%"
                        );
                }

                if ($filters->status === 'active') {
                        $query->where(
                                'clinics.active',
                                true
                        );
                }

                if ($filters->status === 'inactive') {
                        $query->where(
                                'clinics.active',
                                false
                        );
                }
        }

        private function summary(ClinicsReportTableFiltersData $filters): array
        {
                $query = Clinic::query()
                        ->where(
                                'clinics.university_id',
                                $filters->universityId
                        );

                $this->applySummaryFilters($query, $filters);

                $scheduleQuery = function ($query) use ($filters) {
                        if ($filters->periodId) {
                                $query->where(
                                        'period_id',
                                        $filters->periodId
                                );
                        }
                };

                return [
                        'total' => (clone $query)
                                ->count('clinics.id'),

                        'active' => (clone $query)
                                ->where('clinics.active', true)
                                ->count('clinics.id'),

                        'inactive' => (clone $query)
                                ->where('clinics.active', false)
                                ->count('clinics.id'),

                        'with_schedule' => (clone $query)
                                ->whereHas(
                                        'scheduleSlots',
                                        $scheduleQuery
                                )
                                ->count('clinics.id'),

                        'without_schedule' => (clone $query)
                                ->whereDoesntHave(
                                        'scheduleSlots',
                                        $scheduleQuery
                                )
                                ->count('clinics.id'),
                ];
        }

        private function applySummaryFilters(Builder $query, ClinicsReportTableFiltersData $filters): void
        {
                if ($filters->search) {
                        $query->where(
                                'clinics.name',
                                'ilike',
                                "%{$filters->search}%"
                        );
                }
        }

        private function resolveSortField(string $sortField): string
        {
                return match ($sortField) {
                        'name' => 'clinics.name',
                        default => 'clinics.created_at',
                };
        }
}
