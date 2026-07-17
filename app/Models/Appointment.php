<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'schedule_enrollment_id',
        'patient_id',
        'student_id',
        'procedure_id',
        'responsible_id',
        'scheduled_start_at',
        'scheduled_end_at',
        'status',
        'notes',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(ScheduleEnrollment::class, 'schedule_enrollment_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function slot()
    {
        return $this->hasOneThrough(
            ScheduleSlot::class,
            ScheduleEnrollment::class,
            'id',
            'id',
            'schedule_enrollment_id',
            'schedule_slot_id'
        );
    }
}