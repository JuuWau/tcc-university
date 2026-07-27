<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentService
{
        public function listByStudent(int $studentId, ?int $clinicId = null, ?string $date = null,)
        {
                $appointments = Appointment::query()
                        ->with([
                                'patient',
                                'enrollment.slot.clinic',
                        ])
                        ->where('student_id', $studentId)
                        ->when(
                                $date,
                                fn($query) => $query->whereDate(
                                        'scheduled_start_at',
                                        $date,
                                )
                        )
                        ->when(
                                $clinicId,
                                fn($query) => $query->whereHas(
                                        'enrollment.slot',
                                        fn($q) => $q->where('clinic_id', $clinicId)
                                )
                        )
                        ->orderBy('scheduled_start_at')
                        ->get();


                $slot = ScheduleSlot::query()
                        ->where('clinic_id', $clinicId)
                        ->when(
                                $date,
                                fn($query) => $query->whereDate('date', $date)
                        )
                        ->first();


                $enrollment = ScheduleEnrollment::query()
                        ->where('student_id', $studentId)
                        ->when(
                                $slot,
                                fn($query) => $query->where(
                                        'schedule_slot_id',
                                        $slot->id
                                )
                        )
                        ->first();


                return [
                        'slot' => $slot,
                        'schedule_enrollment_id' => $enrollment?->id,
                        'appointments' => $appointments,
                ];
        }

        public function updateTime(Appointment $appointment, Carbon $scheduledStartAt, Carbon $scheduledEndAt): Appointment
        {
                $hasConflict = Appointment::query()
                        ->where('student_id', $appointment->student_id)
                        ->where('id', '!=', $appointment->id)
                        ->where(function ($query) use ($scheduledStartAt, $scheduledEndAt) {
                                $query
                                        ->where('scheduled_start_at', '<', $scheduledEndAt)
                                        ->where('scheduled_end_at', '>', $scheduledStartAt);
                        })
                        ->exists();

                if ($hasConflict) {
                        throw new \DomainException(
                                'Já existe um agendamento neste horário.'
                        );
                }

                return DB::transaction(function () use ($appointment, $scheduledStartAt, $scheduledEndAt) {

                        $oldStartAt = $appointment->scheduled_start_at;
                        $oldEndAt = $appointment->scheduled_end_at;

                        $appointment->scheduled_start_at = $scheduledStartAt;
                        $appointment->scheduled_end_at = $scheduledEndAt;

                        $changes = ActivityLogService::getChanges($appointment);

                        $appointment->save();

                        $oldStartFormatted = $oldStartAt
                                ? $oldStartAt->format('d/m/Y H:i')
                                : 'N/A';

                        $oldEndFormatted = $oldEndAt
                                ? $oldEndAt->format('d/m/Y H:i')
                                : 'N/A';

                        $description = sprintf(
                                "Horário do agendamento do paciente '%s' alterado de %s - %s para %s - %s.",
                                $appointment->patient?->name,
                                $oldStartFormatted,
                                $oldEndFormatted,
                                $scheduledStartAt->format('d/m/Y H:i'),
                                $scheduledEndAt->format('d/m/Y H:i')
                        );

                        ActivityLogService::updated(
                                ActivityModules::APPOINTMENTS,
                                $description,
                                $appointment,
                                $changes,
                        );

                        return $appointment->fresh([
                                'patient',
                                'enrollment.slot',
                        ]);
                });
        }

        public function updateAppointment(Appointment $appointment, array $data): Appointment
        {
                return DB::transaction(function () use ($appointment, $data) {

                        $oldAppointment = $appointment->fresh();

                        $scheduledStartAt = Carbon::parse($data['scheduled_start_at']);

                        $scheduledEndAt = Carbon::parse($data['scheduled_end_at']);

                        $enrollment = ScheduleEnrollment::query()
                                ->where('student_id', $appointment->student_id)
                                ->whereHas('slot', function ($query) use ($scheduledStartAt) {
                                        $query->whereDate(
                                                'date',
                                                $scheduledStartAt->toDateString()
                                        );
                                })
                                ->first();

                        if (!$enrollment) {
                                throw new \DomainException(
                                        'Não existe agenda aberta para esta data.'
                                );
                        }

                        $hasConflict = Appointment::query()
                                ->where('student_id', $appointment->student_id)
                                ->where('id', '!=', $appointment->id)
                                ->where(
                                        fn($query) => $query
                                                ->where('scheduled_start_at', '<', $scheduledEndAt)
                                                ->where('scheduled_end_at', '>', $scheduledStartAt)
                                )
                                ->exists();

                        if ($hasConflict) {
                                throw new \DomainException(
                                        'Já existe um agendamento neste horário.'
                                );
                        }

                        $appointment->fill([
                                'schedule_enrollment_id' => $enrollment->id,
                                'patient_id' => $data['patient_id'],
                                'procedure_id' => $data['procedure_id'] ?? null,
                                'status' => $data['status'],
                                'scheduled_start_at' => $scheduledStartAt,
                                'scheduled_end_at' => $scheduledEndAt,
                                'notes' => $data['notes'] ?? null,
                        ]);

                        $changes = ActivityLogService::getChanges($appointment);

                        $appointment->save();

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'patient_id',
                                'paciente',
                                Patient::class,
                                $oldAppointment->patient_id,
                                $appointment->patient_id,
                                fn(Patient $patient) => $patient->name ?? "ID: {$patient->id}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'procedure_id',
                                'procedimento',
                                Procedure::class,
                                $oldAppointment->procedure_id,
                                $appointment->procedure_id,
                                fn(Procedure $procedure) => $procedure->name ?? "ID: {$procedure->id}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'schedule_enrollment_id',
                                'agenda',
                                ScheduleEnrollment::class,
                                $oldAppointment->schedule_enrollment_id,
                                $appointment->schedule_enrollment_id,
                                fn(ScheduleEnrollment $enrollment) =>
                                $enrollment->slot?->clinic?->name
                                        . ' - '
                                        . $enrollment->slot?->period?->name
                                        . ' - '
                                        . $enrollment->slot?->date
                                        . ' '
                                        . $enrollment->slot?->start_time
                                        . ' - '
                                        . $enrollment->slot?->end_time,
                        );

                        ActivityLogService::updated(
                                ActivityModules::APPOINTMENTS,
                                "Atualizou o agendamento do paciente '{$appointment->patient?->code} - {$appointment->patient?->name}'.",
                                $appointment,
                                $changes,
                        );

                        return $appointment->fresh([
                                'patient',
                                'procedure',
                                'enrollment.slot',
                        ]);
                });
        }

        public function createAppointment( int $studentId, array $data,): Appointment 
        {
                return DB::transaction(function () use ($studentId, $data) {

                        $scheduledStartAt = Carbon::parse($data['scheduled_start_at']);

                        $scheduledEndAt = Carbon::parse($data['scheduled_end_at']);

                        $enrollment = ScheduleEnrollment::query()
                                ->where('student_id', $studentId)
                                ->whereHas('slot', function ($query) use ($scheduledStartAt) {
                                        $query->whereDate(
                                                'date',
                                                $scheduledStartAt->toDateString()
                                        );
                                })
                                ->first();

                        if (!$enrollment) {
                                throw new \DomainException(
                                        'Não existe agenda aberta para esta data.'
                                );
                        }

                        $hasConflict = Appointment::query()
                                ->where('student_id', $studentId)
                                ->where(
                                        fn($query) => $query
                                                ->where('scheduled_start_at', '<', $scheduledEndAt)
                                                ->where('scheduled_end_at', '>', $scheduledStartAt)
                                )
                                ->exists();

                        if ($hasConflict) {
                                throw new \DomainException(
                                        'Já existe um agendamento neste horário.'
                                );
                        }

                        $appointment = Appointment::create([
                                'student_id' => $studentId,
                                'patient_id' => $data['patient_id'],
                                'procedure_id' => $data['procedure_id'],
                                'status' => $data['status'],
                                'scheduled_start_at' => $scheduledStartAt,
                                'scheduled_end_at' => $scheduledEndAt,
                                'notes' => $data['notes'] ?? null,
                                'schedule_enrollment_id' => $enrollment->id,
                        ]);

                        $changes = ActivityLogService::getCreatedChanges($appointment);

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'patient_id',
                                'paciente',
                                Patient::class,
                                null,
                                $appointment->patient_id,
                                fn(Patient $patient) => $patient->name ?? "ID: {$patient->id}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'procedure_id',
                                'procedimento',
                                Procedure::class,
                                null,
                                $appointment->procedure_id,
                                fn(Procedure $procedure) => $procedure->name ?? "ID: {$procedure->id}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'schedule_enrollment_id',
                                'agenda',
                                ScheduleEnrollment::class,
                                null,
                                $appointment->schedule_enrollment_id,
                                fn(ScheduleEnrollment $enrollment) =>
                                $enrollment->slot?->clinic?->name
                                        . ' - '
                                        . $enrollment->slot?->period?->name
                                        . ' - '
                                        . $enrollment->slot?->date
                                        . ' '
                                        . $enrollment->slot?->start_time
                                        . ' - '
                                        . $enrollment->slot?->end_time,
                        );

                        ActivityLogService::created(
                                ActivityModules::APPOINTMENTS,
                                "Criou o agendamento do paciente '{$appointment->patient?->code} - {$appointment->patient?->name}'.",
                                $appointment,
                                $changes,
                        );

                        return $appointment->fresh([
                                'patient',
                                'procedure',
                                'enrollment.slot',
                        ]);
                });
        }
}
