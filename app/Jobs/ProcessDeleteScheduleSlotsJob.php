<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\ScheduleEnrollment;
use App\Models\ScheduleSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessDeleteScheduleSlotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $slotIds
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {

            $enrollmentIds = ScheduleEnrollment::query()
                ->whereIn('schedule_slot_id', $this->slotIds)
                ->pluck('id');

            ScheduleEnrollment::query()
                ->whereIn('id', $enrollmentIds)
                ->update([
                    'status' => 'canceled',
                    'updated_at' => now(),
                ]);

            Appointment::query()
                ->whereIn('schedule_enrollment_id', $enrollmentIds)
                ->update([
                    'status' => 'canceled',
                    'updated_at' => now(),
                ]);

            Appointment::query()
                ->whereIn('schedule_enrollment_id', $enrollmentIds)
                ->delete();

            ScheduleEnrollment::query()
                ->whereIn('id', $enrollmentIds)
                ->delete();

            ScheduleSlot::query()
                ->whereIn('id', $this->slotIds)
                ->delete();
        });
    }
}
