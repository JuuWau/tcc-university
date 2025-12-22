<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = [
        'university_id',
        'name',
        'year',
        'active',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'student_periods'
        )->withPivot(['started_at', 'ended_at', 'is_current'])
            ->withTimestamps();
    }

    public function specialties()
    {
        return $this->belongsToMany(
            Specialty::class,
            'period_specialty'
        )->withTimestamps();
    }
}
