<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Http\Resources\AppointmentResource;
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
                                "Horário do agendamento do paciente '%s' alterado",
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
                                'schedule',
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

        public function createAppointment(int $studentId, array $data,): Appointment
        {
                return DB::transaction(function () use ($studentId, $data) {
                        $scheduledStartAt = Carbon::parse($data['scheduled_start_at']);
                        $scheduledEndAt = Carbon::parse($data['scheduled_end_at']);

                        $this->validateStudentBookingDeadline($scheduledStartAt);

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
                                'schedule',
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

        public function getAvailableDays(array $filters): array
        {
                $studentId = $filters['student_id'];

                $slots = ScheduleSlot::query()
                        ->where('clinic_id', $filters['clinic_id'])
                        ->where('period_id', $filters['period_id'])
                        ->whereYear('date', $filters['year'])
                        ->whereMonth('date', $filters['month'])
                        ->whereDate('date', '>=', today())
                        ->whereHas('enrollments', function ($query) use ($studentId) {
                                $query
                                        ->where('student_id', $studentId)
                                        ->where(
                                                'status',
                                                ScheduleEnrollment::STATUS_ACTIVE
                                        );
                        })
                        ->orderBy('date')
                        ->get();

                return $slots
                        ->pluck('date')
                        ->map(fn($date) => $date->format('Y-m-d'))
                        ->unique()
                        ->values()
                        ->all();
        }

        public function getAvailableTimes(array $filters,): array
        {
                $studentId = $filters['student_id'];

                $slots = ScheduleSlot::query()
                        ->with([
                                'enrollments' => function ($query) use ($studentId) {
                                        $query
                                                ->where('student_id', $studentId)
                                                ->where(
                                                        'status',
                                                        ScheduleEnrollment::STATUS_ACTIVE
                                                );
                                },
                        ])
                        ->where('clinic_id', $filters['clinic_id'])
                        ->where('period_id', $filters['period_id'])
                        ->whereDate('date', $filters['date'])
                        ->whereHas('enrollments', function ($query) use ($studentId) {
                                $query
                                        ->where('student_id', $studentId)
                                        ->where(
                                                'status',
                                                ScheduleEnrollment::STATUS_ACTIVE
                                        );
                        })
                        ->orderBy('start_time')
                        ->get();

                return $slots
                        ->map(function (ScheduleSlot $slot) {
                                $enrollment = $slot->enrollments->first();

                                return [
                                        'schedule_enrollment_id' => $enrollment?->id,
                                        'start_time' => substr((string) $slot->start_time, 0, 5),
                                        'end_time' => substr((string) $slot->end_time, 0, 5),
                                        'allow_procedure_booking' => $slot->allow_procedure_booking,
                                ];
                        })
                        ->values()
                        ->all();
        }


        public function getCalendarAppointmentsByStudent(array $filters)
        {
                return AppointmentResource::collection(
                        Appointment::query()
                                ->with([
                                        'patient',
                                        'procedure',
                                        'enrollment.slot',
                                ])
                                ->where('student_id', $filters['student_id'])
                                ->whereDate(
                                        'scheduled_start_at',
                                        $filters['date']
                                )
                                ->whereHas('enrollment.slot', function ($query) use ($filters) {
                                        $query
                                                ->where('clinic_id', $filters['clinic_id'])
                                                ->where('period_id', $filters['period_id']);
                                })
                                ->orderBy('scheduled_start_at')
                                ->get()
                );
        }

        public function createPatientAppointment(int $patientId, array $data): Appointment
        {
                return DB::transaction(function () use ($patientId, $data) {

                        $enrollment = ScheduleEnrollment::query()
                                ->with('slot')
                                ->find($data['schedule_enrollment_id']);

                        if (!$enrollment) {
                                throw new \DomainException(
                                        'Não existe agenda para este horário.'
                                );
                        }

                        $scheduledStartAt = Carbon::parse(
                                $data['scheduled_start_at']
                        );

                        $scheduledEndAt = Carbon::parse(
                                $data['scheduled_end_at']
                        );

                        $hasConflict = Appointment::query()
                                ->where('student_id', $enrollment->student_id)
                                ->where(function ($query) use (
                                        $scheduledStartAt,
                                        $scheduledEndAt
                                ) {
                                        $query
                                                ->where(
                                                        'scheduled_start_at',
                                                        '<',
                                                        $scheduledEndAt
                                                )
                                                ->where(
                                                        'scheduled_end_at',
                                                        '>',
                                                        $scheduledStartAt
                                                );
                                })
                                ->exists();

                        if ($hasConflict) {
                                throw new \DomainException(
                                        'Já existe um agendamento neste horário.'
                                );
                        }

                        $appointment = Appointment::create([
                                'student_id' => $enrollment->student_id,
                                'patient_id' => $patientId,
                                'procedure_id' => $data['procedure_id'] ?? null,
                                'status' => $data['status'],
                                'scheduled_start_at' => $scheduledStartAt,
                                'scheduled_end_at' => $scheduledEndAt,
                                'notes' => $data['notes'] ?? null,
                                'schedule_enrollment_id' => $enrollment->id,
                        ]);

                        return $appointment->fresh([
                                'patient',
                                'procedure',
                                'enrollment.slot',
                        ]);
                });
        }

        public function updatePatientAppointment(int $patient, Appointment $appointment, array $data,): Appointment
        {
                return DB::transaction(function () use (
                        $patient,
                        $appointment,
                        $data
                ) {
                        if ($appointment->patient_id !== $patient) {
                                throw new \DomainException(
                                        'Este agendamento não pertence ao paciente informado.'
                                );
                        }

                        $oldAppointment = $appointment->fresh();

                        $scheduledStartAt = Carbon::parse(
                                $data['scheduled_start_at']
                        );

                        $scheduledEndAt = Carbon::parse(
                                $data['scheduled_end_at']
                        );

                        $enrollment = ScheduleEnrollment::query()
                                ->where('id', $appointment->schedule_enrollment_id)
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
                                                ->where(
                                                        'scheduled_start_at',
                                                        '<',
                                                        $scheduledEndAt
                                                )
                                                ->where(
                                                        'scheduled_end_at',
                                                        '>',
                                                        $scheduledStartAt
                                                )
                                )
                                ->exists();

                        if ($hasConflict) {
                                throw new \DomainException(
                                        'Já existe um agendamento neste horário.'
                                );
                        }

                        $appointment->fill([
                                'procedure_id' => $data['procedure_id'] ?? null,
                                'status' => $data['status'],
                                'scheduled_start_at' => $scheduledStartAt,
                                'scheduled_end_at' => $scheduledEndAt,
                                'notes' => $data['notes'] ?? null,
                        ]);

                        $changes = ActivityLogService::getChanges(
                                $appointment
                        );

                        $appointment->save();

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'procedure_id',
                                'procedimento',
                                Procedure::class,
                                $oldAppointment->procedure_id,
                                $appointment->procedure_id,
                                fn(Procedure $procedure) =>
                                $procedure->name ?? "ID: {$procedure->id}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'schedule_enrollment_id',
                                'schedule',
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

        private function validateStudentBookingDeadline(Carbon $scheduledStartAt): void
        {
                $user = auth()->user();

                if (!$user?->hasRole('student')) {
                        return;
                }

                $minimumDateTime = now()->addHours(24);

                if ($scheduledStartAt->lt($minimumDateTime)) {
                        throw new \DomainException(
                                'Alunos devem realizar agendamentos com pelo menos 24 horas de antecedência.'
                        );
                }
        }
}
