<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ScheduleEnrollment;
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
                        fn ($query) => $query->whereDate(
                                'scheduled_start_at',
                                $date,
                        )
                        )
                        ->when(
                        $clinicId,
                        fn ($query) => $query->whereHas(
                                'enrollment.slot',
                                fn ($q) => $q->where(
                                'clinic_id',
                                $clinicId,
                                )
                        )
                        )
                        ->orderBy('scheduled_start_at')
                        ->get();

                $enrollment = ScheduleEnrollment::query()
                        ->with('slot')
                        ->where('student_id', $studentId)
                        ->when(
                        $clinicId,
                        fn ($query) => $query->whereHas(
                                'slot',
                                fn ($q) => $q->where(
                                'clinic_id',
                                $clinicId,
                                )
                        )
                        )
                        ->when(
                        $date,
                        fn ($query) => $query->whereHas(
                                'slot',
                                fn ($q) => $q->whereDate(
                                'date',
                                $date,
                                )
                        )
                        )
                        ->first();

                return [
                        'slot' => $enrollment?->slot,
                        'schedule_enrollment_id' => $enrollment?->id,
                        'appointments' => $appointments,
                ];
        }

        public function updateTime(Appointment $appointment, Carbon $scheduledStartAt,Carbon $scheduledEndAt,): Appointment
        {
                $hasConflict = Appointment::query()
                        ->where('student_id', $appointment->student_id)
                        ->where('id', '!=', $appointment->id)
                        ->where(function ($query) use (
                        $scheduledStartAt,
                        $scheduledEndAt
                        ) {
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

                $appointment->update([
                        'scheduled_start_at' => $scheduledStartAt,
                        'scheduled_end_at' => $scheduledEndAt,
                ]);

                return $appointment->fresh([
                        'patient',
                        'enrollment.slot',
                ]);
        }

        public function updateAppointment(Appointment $appointment, array $data): Appointment 
        {
                $scheduledStartAt = Carbon::parse($data['scheduled_start_at']);

                $scheduledEndAt = Carbon::parse(
                        $data['scheduled_end_at']
                );

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
                        fn ($query) => $query
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

                $appointment->update([
                        'schedule_enrollment_id' => $enrollment->id,
                        'patient_id' => $data['patient_id'],
                        'procedure_id' => $data['procedure_id'] ?? null,
                        'status' => $data['status'],
                        'scheduled_start_at' => $scheduledStartAt,
                        'scheduled_end_at' => $scheduledEndAt,
                        'notes' => $data['notes'] ?? null,
                ]);

                return $appointment->fresh([
                        'patient',
                        'procedure',
                        'enrollment.slot',
                ]);
        }

        public function createAppointment(int $studentId, array $data,
        ): Appointment 
        {
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
                        fn ($query) => $query
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
                        'schedule_enrollment_id' => $data['schedule_enrollment_id'],
                ]);

                return $appointment->fresh([
                        'patient',
                        'procedure',
                        'enrollment.slot',
                ]);
        }
}
