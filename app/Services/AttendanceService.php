<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class AttendanceService
{
        public function listAvailableClinics(User $user): Collection
        {
                $query = Clinic::query()
                        ->where('university_id', $user->university_id)
                        ->whereHas('scheduleSlots');

                if (!$user->hasRole('admin')) {
                        $query->whereHas('scheduleSlots.responsibles', function ($query) use ($user) {
                                $query->where('users.id', $user->id);
                        });
                }

                return $query
                        ->orderBy('name')
                        ->get();
        }

        public function getAvailableDates(Clinic $clinic, int $periodId, User $user): Collection
        {
        return ScheduleSlot::query()
                ->where('clinic_id', $clinic->id)
                ->where('period_id', $periodId)
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();
        }

        public function getStudents(ScheduleSlot $slot): Collection 
        {
                return ScheduleEnrollment::query()
                        ->with('student.person')
                        ->where('schedule_slot_id', $slot->id)
                        ->where('status', '!=', ScheduleEnrollment::STATUS_CANCELLED)
                        ->get();
        }

        public function updateAttendance(ScheduleSlot $slot, array $data,): void 
        {
                DB::transaction(function () use ($data, $slot) {
                        foreach ($data['students'] as $studentData) {
                                ScheduleEnrollment::query()
                                        ->whereKey($studentData['id'])
                                        ->where('schedule_slot_id', $slot->id)
                                        ->update([
                                                'status' => $studentData['attended']
                                                ? ScheduleEnrollment::STATUS_ATTENDED
                                                : ScheduleEnrollment::STATUS_MISSED,
                                ]);
                        }
                });
        }
}
