<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;

class MarkPastScheduledAppointmentsAsNoShowAction
{
    /**
     * Create a new class instance.
     */
    public function execute(): int
    {
        return Appointment::query()
            ->where('status', 'scheduled')
            ->where('scheduled_end_at', '<', now())
            ->update([
                'status' => 'no_show',
            ]);
    }
}
