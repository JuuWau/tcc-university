<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'person_id',
        'university_id',
        'registration',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
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
}
