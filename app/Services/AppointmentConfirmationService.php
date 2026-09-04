<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Appointment;
use App\Models\ScheduleEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AppointmentConfirmationService
{
        public function list(User $user, array $filters = [])
        {
                $query = $this->getAppointmentsQuery($user)
                        ->with([
                                'patient',
                                'student',
                                'slot.clinic',
                                'slot.period',
                        ]);

                if (!empty($filters['clinic_id'])) {
                        $query->whereHas(
                                'slot',
                                function (Builder $query) use ($filters) {
                                        $query->where(
                                                'clinic_id',
                                                $filters['clinic_id']
                                        );
                                }
                        );
                }

                if (!empty($filters['period_id'])) {
                        $query->whereHas(
                                'slot',
                                function (Builder $query) use ($filters) {
                                        $query->where(
                                                'period_id',
                                                $filters['period_id']
                                        );
                                }
                        );
                }

                if (!empty($filters['date'])) {
                        $query->whereDate(
                                'scheduled_start_at',
                                $filters['date']
                        );
                }

                if (!empty($filters['status'])) {
                        $query->where(
                                'status',
                                $filters['status']
                        );
                }

                return $query
                        ->orderBy('scheduled_start_at')
                        ->get();
        }

        public function updateStatus(Appointment $appointment, string $status): Appointment
        {

                return DB::transaction(function () use ($appointment, $status) {

                        $appointment->fill([
                                'status' => $status,
                        ]);

                        $changes = ActivityLogService::getChanges($appointment);

                        $appointment->save();

                        ActivityLogService::updated(
                                ActivityModules::APPOINTMENTS,
                                "Atualizou o status do agendamento do paciente '{$appointment->patient?->code} - {$appointment->patient?->name}'.",
                                $appointment,
                                $changes,
                        );

                        return $appointment->refresh();
                });
        }

        public function getAvailableClinics(User $user, ?int $universityId)
        {
                return $this->getAppointmentsQuery($user)
                        ->with('slot.clinic')
                        ->get()
                        ->pluck('slot.clinic')
                        ->filter()
                        ->filter(function ($clinic) use ($universityId) {
                                return $clinic->active
                                        && (
                                                !$universityId
                                                || $clinic->university_id === $universityId
                                        );
                        })
                        ->unique('id')
                        ->sortBy('name')
                        ->values()
                        ->map(fn($clinic) => [
                                'id' => $clinic->id,
                                'label' => $clinic->name,
                        ]);
        }

        public function getAvailablePeriods(User $user, ?int $universityId)
        {
                if ($user->hasPermissionTo('appointments-confirmation-student.view')) {
                        return $user->student
                                ?->periods()
                                ->when(
                                        $universityId,
                                        fn(Builder $query) => $query->where(
                                                'university_id',
                                                $universityId
                                        )
                                )
                                ->orderByDesc('calendar_year')
                                ->orderByDesc('semester')
                                ->get()
                                ->map(fn($period) => [
                                        'id' => $period->id,
                                        'label' => sprintf(
                                                '%dº ano - %dº semestre - %d',
                                                $period->academic_year,
                                                $period->semester,
                                                $period->calendar_year,
                                        ),
                                ])
                                ?? collect();
                }
        }

        private function getAppointmentsQuery(User $user): Builder
        {
                $query = Appointment::query();

                if ($user->can('appointments-confirmation-student.view')) {
                        $query->whereHas(
                                'student',
                                fn(Builder $query) => $query->where(
                                        'user_id',
                                        $user->id
                                )
                        );
                }

                return $query;
        }
}
