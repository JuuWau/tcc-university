<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Appointment;
use App\Models\ScheduleEnrollment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentConfirmationService
{
        public function list(array $filters = [])
        {
                $date = $filters['date']
                        ?? now()->toDateString();

                $query = Appointment::query()
                        ->with([
                                'student',
                                'patient',
                                'responsible',
                                'procedure',
                                'enrollment.slot.clinic',
                                'enrollment.slot.period',
                        ])
                        ->whereDate(
                                'scheduled_start_at',
                                $date
                        );

                if (!empty($filters['clinic_id'])) {
                        $query->whereHas(
                                'enrollment.slot',
                                fn($q) => $q->where(
                                        'clinic_id',
                                        $filters['clinic_id']
                                )
                        );
                }

                if (!empty($filters['period_id'])) {
                        $query->whereHas(
                                'enrollment.slot',
                                fn($q) => $q->where(
                                        'period_id',
                                        $filters['period_id']
                                )
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
}
