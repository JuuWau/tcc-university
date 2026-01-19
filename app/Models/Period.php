<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'university_id',
        'calendar_year',
        'academic_year',
        'semester',
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
        )->withPivot(['started_at', 'ended_at', 'is_current']);
    }

    public function specialties()
    {
        return $this->belongsToMany(
            Specialty::class,
            'period_specialty'
        )->withTimestamps();
    }
}
