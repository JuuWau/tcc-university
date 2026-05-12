<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\DB;

class ScheduleEnrollmentService
{
        public function attachStudents(array $slotIds, array $studentIds, int $universityId): void
        {
                $slots = ScheduleSlot::query()
                        ->where('university_id', $universityId)
                        ->whereIn('id', $slotIds)
                        ->get();

                foreach ($slots as $slot) {
                        foreach ($studentIds as $studentId) {

                        $enrollment = ScheduleEnrollment::withTrashed()
                                ->where('schedule_slot_id', $slot->id)
                                ->where('student_id', $studentId)
                                ->first();

                        if ($enrollment) {
                                if ($enrollment->trashed()) {
                                $enrollment->restore();
                                }

                                continue;
                        }

                        ScheduleEnrollment::create([
                                'schedule_slot_id' => $slot->id,
                                'student_id' => $studentId,
                                'status' => ScheduleEnrollment::STATUS_ACTIVE,
                                ]);
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
        $enrollment = ScheduleEnrollment::where('schedule_slot_id', $slotId)
                ->where('student_id', $studentId)
                ->first();

        if (! $enrollment) {
                return;
        }

        $enrollment->update([
                'status' => 'cancelled',
        ]);

        Appointment::where('schedule_enrollment_id', $enrollment->id)
                ->update([
                'status' => 'canceled',
                ]);

        $enrollment->delete();

        Appointment::where('schedule_enrollment_id', $enrollment->id)
                ->delete();
        }
}
