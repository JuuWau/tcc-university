<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientStudent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'student_id',
    ];
}
