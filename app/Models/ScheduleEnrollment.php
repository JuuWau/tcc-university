<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleEnrollment extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_MISSED = 'missed';
    public const STATUS_ATTENDED = 'attended';

    protected $fillable = [
        'schedule_slot_id',
        'student_id',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    public function slot()
    {
        return $this->belongsTo(ScheduleSlot::class, 'schedule_slot_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}