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
        'responsible_id',
        'date',
        'start_time',
        'end_time',
        'available_slots',
        'allow_student_booking',
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

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ScheduleEnrollment::class);
    }
}
