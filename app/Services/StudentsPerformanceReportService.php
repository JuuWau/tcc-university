<?php

namespace App\Services;

use App\Data\StudentsPerformanceReport\StudentsPerformanceReportTableFiltersData;
use App\Models\Clinic;
use App\Models\Period;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentsPerformanceReportService
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
                                                'name' => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}"
                                        ];
                                })
                                ->all(),
                        'clinics' => Clinic::query()
                                ->where('university_id', $universityId)
                                ->where('active', true)
                                ->get([
                                        'id',
                                        'name',
                                ])
                ];
        }

        public function paginate(StudentsPerformanceReportTableFiltersData $filters): array
        {
                $query = $this->baseQuery($filters);

                $this->applyFilters($query, $filters);

                $students = $query
                        ->orderBy(
                                $this->resolveSortField($filters->sortField),
                                $filters->sortDir
                        )
                        ->paginate(
                                $filters->perPage,
                                ['*'],
                                'page',
                                $filters->page
                        );

                return [
                        'students' => $students,
                        'summary' => $this->summary($filters),
                ];
        }

        public function studentsForExport(StudentsPerformanceReportTableFiltersData $filters): Builder
        {
                $query = $this->baseQuery($filters);

                $this->applyFilters($query, $filters);

                return $query->orderBy(
                        $this->resolveSortField($filters->sortField),
                        $filters->sortDir
                );
        }

        private function baseQuery(StudentsPerformanceReportTableFiltersData $filters): Builder
        {
                return Student::query()
                        ->select([
                                'students.*',
                                'clinics.id as clinic_id',
                                'clinics.name as clinic_name',
                                'periods.id as period_id',
                                'periods.academic_year',
                                'periods.semester',
                                'periods.calendar_year',
                        ])
                        ->selectRaw('COUNT(appointments.id) AS total_appointments')
                        ->join(
                                'people',
                                'people.id',
                                '=',
                                'students.person_id'
                        )
                        ->leftJoin(
                                'schedule_enrollments',
                                'schedule_enrollments.student_id',
                                '=',
                                'students.id'
                        )
                        ->leftJoin(
                                'schedule_slots',
                                'schedule_slots.id',
                                '=',
                                'schedule_enrollments.schedule_slot_id'
                        )
                        ->leftJoin(
                                'clinics',
                                'clinics.id',
                                '=',
                                'schedule_slots.clinic_id'
                        )
                        ->leftJoin(
                                'periods',
                                'periods.id',
                                '=',
                                'schedule_slots.period_id'
                        )
                        ->leftJoin(
                                'appointments',
                                function ($join) {
                                        $join
                                                ->on(
                                                        'appointments.schedule_enrollment_id',
                                                        '=',
                                                        'schedule_enrollments.id'
                                                )
                                                ->on(
                                                        'appointments.student_id',
                                                        '=',
                                                        'students.id'
                                                )
                                                ->where(
                                                        'appointments.status',
                                                        'completed'
                                                );
                                }
                        )
                        ->with([
                                'person',
                                'user.invite',
                        ])
                        ->where(
                                'students.university_id',
                                $filters->universityId
                        )
                        ->groupBy(
                                'students.id',
                                'people.name',
                                'students.registration',
                                'clinics.id',
                                'clinics.name',
                                'periods.id',
                                'periods.academic_year',
                                'periods.semester',
                                'periods.calendar_year'
                        );
        }

        private function applyFilters(Builder $query, StudentsPerformanceReportTableFiltersData $filters): void
        {
                if ($filters->search) {
                        $search = $filters->search;

                        $query->where(function (Builder $query) use ($search) {
                                $query
                                        ->where(
                                                'people.name',
                                                'ilike',
                                                "%{$search}%"
                                        )
                                        ->orWhere(
                                                'students.registration',
                                                'ilike',
                                                "%{$search}%"
                                        )->whereHas('user.invite', function (Builder $query) {
                                                $query->whereNotNull('used_at');
                                        });
                        });
                }

                if ($filters->periodId) {
                        $query->where(
                                'schedule_slots.period_id',
                                $filters->periodId
                        );
                }

                if ($filters->clinicId) {
                        $query->where('schedule_slots.clinic_id', $filters->clinicId);
                }
        }

        private function summary(StudentsPerformanceReportTableFiltersData $filters): array
        {
                $query = $this->baseQuery($filters);

                $this->applySummaryFilters($query, $filters);

                return [
                        'total' => (clone $query)
                                ->distinct()
                                ->count('students.id'),

                        'active' => (clone $query)
                                ->whereNull('students.deleted_at')
                                ->distinct()
                                ->count('students.id'),

                        'inactive' => (clone $query)
                                ->whereNotNull('students.deleted_at')
                                ->distinct()
                                ->count('students.id'),

                        'invitation_accepted' => (clone $query)
                                ->whereHas('user.invite', function (Builder $query) {
                                        $query->whereNotNull('used_at');
                                })
                                ->distinct()
                                ->count('students.id'),

                        'invitation_pending' => (clone $query)
                                ->whereHas('user.invite', function (Builder $query) {
                                        $query->whereNull('used_at');
                                })
                                ->distinct()
                                ->count('students.id'),
                ];
        }

        private function applySummaryFilters(Builder $query, StudentsPerformanceReportTableFiltersData $filters): void
        {
                if ($filters->search) {
                        $search = $filters->search;

                        $query->where(function (Builder $query) use ($search) {
                                $query
                                        ->where(
                                                'people.name',
                                                'ilike',
                                                "%{$search}%"
                                        )
                                        ->orWhere(
                                                'students.registration',
                                                'ilike',
                                                "%{$search}%"
                                        );
                        });
                }

                if ($filters->periodId) {
                        $query->where(
                                'schedule_slots.period_id',
                                $filters->periodId
                        );
                }
        }

        private function resolveSortField(string $sortField): string
        {
                return match ($sortField) {
                        'name' => 'people.name',
                        'registration' => 'students.registration',
                        default => 'students.created_at',
                };
        }
}
