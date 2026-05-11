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
        'responsible_id',
        'scheduled_at',
        'status',
        'notes',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
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
}