<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'university_id',
        'registration',
        'user_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class)->withTrashed();
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function periods()
    {
        return $this->belongsToMany(
            Period::class,
            'student_periods'
        )->withPivot(['started_at', 'ended_at', 'is_current'])
            ->withTimestamps();
    }

    public function currentPeriod()
    {
        return $this->hasOne(StudentPeriod::class)
            ->where('is_current', true);
    }

    public function patients()
    {
        return $this->belongsToMany(
            Patient::class,
            'patient_students'
        )->wherePivotNull('deleted_at');
    }

    public function enrollments()
    {
        return $this->hasMany(ScheduleEnrollment::class);
    }
}
