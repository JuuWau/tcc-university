<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessOpenScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $slots,
        public array $data
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {

            foreach ($this->slots as $slot) {

                $slotModel = ScheduleSlot::find($slot['id']);

                if (!$slotModel) {
                    continue;
                }
                if ($this->data['allow_student_enrollment']) {

                    $students = Student::query()
                        ->select('students.*')
                        ->join(
                            'student_periods',
                            'student_periods.student_id',
                            '=',
                            'students.id'
                        )
                        ->where(
                            'student_periods.period_id',
                            $slot->period_id
                        )
                        ->where('student_periods.is_current', true)
                        ->whereNull('student_periods.ended_at')
                        ->get();

                    $enrollments = [];

                    foreach ($students as $student) {
                        $enrollments[] = [
                            'schedule_slot_id' => $slotModel->id,
                            'student_id' => $student->id,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    ScheduleEnrollment::withTrashed()->upsert(
                        $enrollments,
                        ['schedule_slot_id', 'student_id'],
                        ['status', 'updated_at']
                    );
                }
            }
        });
    }
}
