<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleSlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'university_id',
        'period_id',
        'clinic_id',
        'date',
        'start_time',
        'end_time',
        'available_slots',
        'allow_student_booking',
        'allow_student_enrollment',
        'allow_procedure_booking',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function responsibles()
    {
        return $this->belongsToMany(
            User::class,
            'schedule_slots_responsibles',
            'schedule_slot_id',
            'user_id'
        )->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(ScheduleEnrollment::class);
    }
}
