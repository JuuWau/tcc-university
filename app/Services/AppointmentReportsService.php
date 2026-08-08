<?php

namespace App\Services;

use App\Data\AppointmentReport\AppointmentsReportTableFiltersData;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AppointmentReportsService
{
        public function paginate(AppointmentsReportTableFiltersData $filters): array
        {
                return [
                        'summary' => $this->summary($filters),
                        'appointments' => $this->appointments($filters),
                ];
        }

        private function summary(AppointmentsReportTableFiltersData $filters): array
        {
                $query = Appointment::query();

                $this->applyFilters($query, $filters);

                return [
                        'total' => (clone $query)->count(),

                        'scheduled' => (clone $query)
                                ->where('status', 'scheduled')
                                ->count(),

                        'confirmed' => (clone $query)
                                ->where('status', 'confirmed')
                                ->count(),

                        'completed' => (clone $query)
                                ->where('status', 'completed')
                                ->count(),

                        'canceled' => (clone $query)
                                ->where('status', 'canceled')
                                ->count(),

                        'no_show' => (clone $query)
                                ->where('status', 'no_show')
                                ->count(),

                        'rescheduled' => (clone $query)
                                ->where('status', 'rescheduled')
                                ->count(),
                ];
        }

        private function appointments(AppointmentsReportTableFiltersData $filters)
        {
                $query = Appointment::query()
                        ->with([
                                'patient',
                                'enrollment.slot.period',
                                'student.user.person',
                                'procedure',
                                'slot.clinic',
                                'slot.responsibles.person',
                        ]);

                $this->applyFilters($query, $filters);

                return $query
                        ->orderBy($filters->sortField, $filters->sortDir)
                        ->paginate(
                                $filters->perPage,
                                ['*'],
                                'page',
                                $filters->page
                        );
        }

        private function applyFilters($query, AppointmentsReportTableFiltersData $filters): void
        {
                $query->whereHas('slot.clinic', function ($query) use ($filters) {
                        $query->where('university_id', $filters->universityId);
                });

                $query->when($filters->clinicId, function ($query) use ($filters) {
                        $query->whereHas('slot', function ($query) use ($filters) {
                                $query->where('clinic_id', $filters->clinicId);
                        });
                });

                $query->when($filters->responsibleId, function ($query) use ($filters) {
                        $query->whereHas('slot.responsibles', function ($query) use ($filters) {
                                $query->where('users.id', $filters->responsibleId);
                        });
                });;

                $query->when($filters->studentId, function ($query) use ($filters) {
                        $query->where('student_id', $filters->studentId);
                });

                $query->when($filters->patientId, function ($query) use ($filters) {
                        $query->where('patient_id', $filters->patientId);
                });

                $query->when($filters->periodId, function ($query) use ($filters) {
                        $query->whereHas('slot', function ($query) use ($filters) {
                                $query->where('period_id', $filters->periodId);
                        });
                });

                $query->when($filters->status, function ($query) use ($filters) {
                        $query->where('status', $filters->status);
                });

                $query->when($filters->startDate, function ($query) use ($filters) {
                        $query->whereDate('scheduled_start_at', '>=', $filters->startDate);
                });

                $query->when($filters->endDate, function ($query) use ($filters) {
                        $query->whereDate('scheduled_start_at', '<=', $filters->endDate);
                });

                $query->when($filters->search, function ($query) use ($filters) {
                        $query->where(function ($query) use ($filters) {
                                $query->whereHas('patient', function ($query) use ($filters) {
                                        $query->where('name', 'ilike', "%{$filters->search}%")->orWhere('code', 'ilike', "%{$filters->search}%");
                                })->orWhereHas('student.person', function ($query) use ($filters) {
                                        $query->where('name', 'ilike', "%{$filters->search}%")->orWhere('student.registration', 'ilike', "%{$filters->search}%");
                                });
                        });
                });
        }

        public function filters(int $universityId): array
        {
                return [
                        'clinics' => Clinic::query()
                                ->where('university_id', $universityId)
                                ->select('id', 'name')
                                ->orderBy('name')
                                ->get(),

                        'responsibles' => User::query()
                                ->where('university_id', $universityId)
                                ->whereHas('role', function ($query) {
                                        $query->where('slug', '!=', 'student');
                                })
                                ->with('person:id,user_id,name')
                                ->orderBy('id')
                                ->get()
                                ->map(function ($user) {
                                        return [
                                                'id' => $user->id,
                                                'name' => $user->person?->name,
                                        ];
                                }),

                        'periods' => Period::query()
                                ->where('university_id', $universityId)
                                ->orderByDesc('academic_year')
                                ->orderByDesc('semester')
                                ->get()
                                ->map(function ($period) {
                                        return [
                                                'id' => $period->id,
                                                'name' => "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}"
                                        ];
                                }),
                ];
        }

        public function appointmentsForExport(AppointmentsReportTableFiltersData $filters): Builder 
        {
                $query = Appointment::query()
                        ->with([
                                'patient',
                                'student.user.person',
                                'procedure',
                                'slot.clinic',
                                'slot.period',
                                'slot.responsibles.person',
                        ]);

                $this->applyFilters($query, $filters);

                return $query
                        ->orderBy($filters->sortField, $filters->sortDir);
        }
}
