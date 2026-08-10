<?php

namespace App\Services;

use App\Data\StudentsReport\StudentsReportTableFiltersData;
use App\Models\Period;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentsReportService
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
        ];
    }

    public function paginate(StudentsReportTableFiltersData $filters): array 
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
                ['students.*'],
                'page',
                $filters->page
            );

        return [
            'students' => $students,
            'summary' => $this->summary($filters),
        ];
    }

    public function studentsForExport(StudentsReportTableFiltersData $filters): Builder 
    {
        $query = $this->baseQuery($filters);

        $this->applyFilters($query, $filters);

        return $query->orderBy(
            $this->resolveSortField($filters->sortField),
            $filters->sortDir
        );
    }

    private function baseQuery(StudentsReportTableFiltersData $filters): Builder 
    {
        return Student::query()
            ->select('students.*')
            ->join(
                'people',
                'people.id',
                '=',
                'students.person_id'
            )
            ->leftJoin(
                'student_periods',
                function ($join) {
                    $join
                        ->on(
                            'student_periods.student_id',
                            '=',
                            'students.id'
                        )
                        ->where(
                            'student_periods.is_current',
                            true
                        );
                }
            )
            ->leftJoin(
                'periods',
                'periods.id',
                '=',
                'student_periods.period_id'
            )
            ->with([
                'person',
                'user.invite',
                'currentPeriod.period',
            ])
            ->where(
                'students.university_id',
                $filters->universityId
            );
    }

    private function applyFilters(Builder $query, StudentsReportTableFiltersData $filters): void 
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
                'student_periods.period_id',
                $filters->periodId
            );
        }

        if ($filters->status === 'active') {
            $query->whereNull('students.deleted_at');
        }

        if ($filters->status === 'inactive') {
            $query->whereNotNull('students.deleted_at');
        }

        if ($filters->invitationStatus === 'accepted') {
            $query->whereHas('user.invite', function (Builder $query) {
                $query->whereNotNull('used_at');
            });
        }

        if ($filters->invitationStatus === 'pending') {
            $query->whereHas('user.invite', function (Builder $query) {
                $query->whereNull('used_at');
            });
        }
    }

    private function summary(StudentsReportTableFiltersData $filters): array 
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

    private function applySummaryFilters(Builder $query, StudentsReportTableFiltersData $filters): void 
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
                'student_periods.period_id',
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
