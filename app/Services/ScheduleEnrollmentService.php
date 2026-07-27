<?php

namespace App\Services;

use App\Constants\ActivityModules;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ScheduleEnrollmentService
{
        public function attachStudents(array $slotIds, array $studentIds, int $universityId): void
        {
                $slots = ScheduleSlot::query()
                        ->with([
                                'clinic',
                                'period',
                        ])
                        ->where('university_id', $universityId)
                        ->whereIn('id', $slotIds)
                        ->get();

                foreach ($slots as $slot) {
                        foreach ($studentIds as $studentId) {

                                $enrollment = ScheduleEnrollment::withTrashed()
                                        ->where('schedule_slot_id', $slot->id)
                                        ->where('student_id', $studentId)
                                        ->first();

                                $student = Student::with('person')->find($studentId);

                                $clinicName = $slot->clinic?->name ?? "ID: {$slot->clinic_id}";
                                $periodName = $slot->period
                                        ? "{$slot->period->academic_year}º ano {$slot->period->semester}º semestre de {$slot->period->calendar_year}"
                                        : "ID: {$slot->period_id}";

                                $slotDescription = "{$clinicName} - {$periodName} ({$slot->date} {$slot->start_time} às {$slot->end_time})";

                                if ($enrollment) {
                                        if ($enrollment->trashed()) {
                                                $enrollment->restore();

                                                ActivityLogService::updated(
                                                        ActivityModules::ENROLLMENTS,
                                                        "Estudante {$student?->person?->name} matriculado na clínica {$clinicName}, período {$periodName}, no dia {$slot->date}.",
                                                        $enrollment,
                                                        [
                                                                'status' => [
                                                                        'old' => 'deleted',
                                                                        'new' => ScheduleEnrollment::STATUS_ACTIVE,
                                                                ],
                                                                'slot' => $slotDescription,
                                                        ],
                                                );
                                        }

                                        continue;
                                }

                                $enrollment = ScheduleEnrollment::create([
                                        'schedule_slot_id' => $slot->id,
                                        'student_id' => $studentId,
                                        'status' => ScheduleEnrollment::STATUS_ACTIVE,
                                ]);

                                ActivityLogService::created(
                                        ActivityModules::ENROLLMENTS,
                                        "Matrícula do estudante {$student?->person?->name} adicionada na clínica {$clinicName}, período {$periodName}, no dia {$slot->date}.",
                                        $enrollment,
                                        [
                                                'slot' => $slotDescription,
                                        ],
                                );
                        }
                }
        }

        public function getSlotStudents(int $slotId)
        {
                $slot = ScheduleSlot::with(['enrollments.student.person',])->findOrFail($slotId);

                return $slot->enrollments
                        ->map(function ($enrollment) {
                                return [
                                        'value' => $enrollment->student->id,
                                        'label' =>
                                        $enrollment->student->registration .
                                                ' - ' .
                                                $enrollment->student->person?->name,
                                ];
                        });
        }

        public function removeStudentFromSlot(int $slotId, int $studentId)
        {
                DB::transaction(function () use ($slotId, $studentId) {
                        $enrollment = ScheduleEnrollment::where('schedule_slot_id', $slotId)
                                ->where('student_id', $studentId)
                                ->first();

                        if (!$enrollment) {
                                return;
                        }

                        $slot = ScheduleSlot::with([
                                'clinic',
                                'period',
                        ])->find($slotId);

                        $student = Student::with('person')->find($studentId);

                        $formattedDate = $slot
                                ? \Carbon\Carbon::parse($slot->date)->format('d/m/Y')
                                : null;

                        $clinicName = $slot?->clinic?->name ?? "ID: {$slot?->clinic_id}";
                        $periodName = $slot->period
                                        ? "{$slot->period->academic_year}º ano {$slot->period->semester}º semestre de {$slot->period->calendar_year}"
                                        : "ID: {$slot->period_id}";

                        $changes = ActivityLogService::getChanges($enrollment);

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'schedule_slot_id',
                                'slot',
                                ScheduleSlot::class,
                                $slotId,
                                null,
                                fn(ScheduleSlot $slot) => "{$slot->date} {$slot->start_time} - {$slot->end_time}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'student_id',
                                'estudante',
                                Student::class,
                                $studentId,
                                null,
                                fn(Student $student) => $student->person?->name ?? "ID: {$student->id}",
                        );

                        ActivityLogService::deleted(
                                ActivityModules::ENROLLMENTS,
                                "Matrícula do estudante {$student?->person?->name} removida da clínica {$clinicName}, período {$periodName}, no dia {$formattedDate}.",
                                $enrollment,
                                $changes,
                        );

                        $enrollment->update([
                                'status' => 'canceled',
                        ]);

                        Appointment::where('schedule_enrollment_id', $enrollment->id)
                                ->update([
                                        'status' => 'canceled',
                                ]);

                        Appointment::where('schedule_enrollment_id', $enrollment->id)
                                ->delete();

                        $enrollment->delete();
                });
        }

        public function enrollMultipleSlots(array $slotIds, int $studentId, int $universityId): void
        {
                DB::transaction(function () use ($slotIds, $studentId, $universityId) {
                        $slots = ScheduleSlot::query()
                                ->where('university_id', $universityId)
                                ->whereIn('id', $slotIds)
                                ->get();

                        $student = Student::find($studentId);
                        $enrolledCount = 0;
                        $restoredCount = 0;

                        foreach ($slots as $slot) {
                                if (!$slot->allow_student_booking) {
                                        continue;
                                }

                                $alreadyEnrolled = ScheduleEnrollment::withTrashed()
                                        ->where('schedule_slot_id', $slot->id)
                                        ->where('student_id', $studentId)
                                        ->first();

                                if ($alreadyEnrolled) {
                                        if ($alreadyEnrolled->trashed()) {
                                                $alreadyEnrolled->restore();
                                                $restoredCount++;

                                                $changes = ActivityLogService::getChanges($alreadyEnrolled);
                                                $formattedDate = \Carbon\Carbon::parse($slot->date)->format('d/m/Y');

                                                ActivityLogService::updated(
                                                        ActivityModules::ENROLLMENTS,
                                                        "Estudante {$student->name} restaurada na agenda do dia {$formattedDate}.",
                                                        $alreadyEnrolled,
                                                        $changes,
                                                );
                                        }
                                        continue;
                                }

                                $enrollment = ScheduleEnrollment::create([
                                        'schedule_slot_id' => $slot->id,
                                        'student_id' => $studentId,
                                        'status' => ScheduleEnrollment::STATUS_ACTIVE,
                                ]);

                                $enrolledCount++;

                                $changes = ActivityLogService::getCreatedChanges($enrollment);
                                $formattedDate = \Carbon\Carbon::parse($slot->date)->format('d/m/Y');

                                ActivityLogService::trackBelongsToChange(
                                        $changes,
                                        'schedule_slot_id',
                                        'slot',
                                        ScheduleSlot::class,
                                        null,
                                        $slot->id,
                                        fn(ScheduleSlot $slot) => "{$formattedDate} {$slot->start_time} - {$slot->end_time}",
                                );

                                ActivityLogService::trackBelongsToChange(
                                        $changes,
                                        'student_id',
                                        'estudante',
                                        Student::class,
                                        null,
                                        $studentId,
                                        fn(Student $student) => $student->name ?? "ID: {$student->id}",
                                );

                                ActivityLogService::created(
                                        ActivityModules::ENROLLMENTS,
                                        "Estudante '{$student->name}' matriculado na agenda do dia {$formattedDate}.",
                                        $enrollment,
                                        $changes,
                                );
                        }

                        if ($enrolledCount > 0 || $restoredCount > 0) {
                                $description = "Operação em massa: {$enrolledCount} novas matrículas";
                                if ($restoredCount > 0) {
                                        $description .= ", {$restoredCount} matrículas restauradas";
                                }
                                $description .= " para o estudante '{$student->name}'.";

                                ActivityLogService::created(
                                        ActivityModules::ENROLLMENTS,
                                        $description,
                                        null,
                                        [
                                                'enrolled_count' => $enrolledCount,
                                                'restored_count' => $restoredCount,
                                                'student_id' => $studentId,
                                                'slot_ids' => $slotIds,
                                        ],
                                );
                        }
                });
        }

        public function enrollSlot(int $slotId, int $studentId, int $universityId): void
        {
                DB::transaction(function () use ($slotId, $studentId, $universityId) {
                        $slot = ScheduleSlot::query()
                                ->where('university_id', $universityId)
                                ->where('id', $slotId)
                                ->first();

                        if (!$slot) {
                                return;
                        }

                        if (!$slot->allow_student_booking) {
                                return;
                        }

                        $alreadyEnrolled = ScheduleEnrollment::query()
                                ->where('schedule_slot_id', $slot->id)
                                ->where('student_id', $studentId)
                                ->exists();

                        if ($alreadyEnrolled) {
                                return;
                        }

                        $student = Student::find($studentId);
                        $enrollment = ScheduleEnrollment::create([
                                'schedule_slot_id' => $slot->id,
                                'student_id' => $studentId,
                                'status' => ScheduleEnrollment::STATUS_ACTIVE,
                        ]);

                        $changes = ActivityLogService::getCreatedChanges($enrollment);
                        $formattedDate = \Carbon\Carbon::parse($slot->date)->format('d/m/Y');

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'schedule_slot_id',
                                'slot',
                                ScheduleSlot::class,
                                null,
                                $slot->id,
                                fn(ScheduleSlot $slot) => "{$formattedDate} {$slot->start_time} - {$slot->end_time}",
                        );

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'student_id',
                                'estudante',
                                Student::class,
                                null,
                                $studentId,
                                fn(Student $student) => $student->name ?? "ID: {$student->id}",
                        );

                        ActivityLogService::created(
                                ActivityModules::ENROLLMENTS,
                                "Estudante '{$student->registration} - {$student->person?->name}' matriculado na agenda do dia {$formattedDate}.",
                                $enrollment,
                                $changes,
                        );
                });
        }
}
