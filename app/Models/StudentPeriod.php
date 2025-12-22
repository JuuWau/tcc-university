<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPeriod extends Model
{
    protected $fillable = [
        'student_id',
        'period_id',
        'started_at',
        'ended_at',
        'is_current',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}
