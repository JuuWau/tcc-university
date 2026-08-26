<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicSpecialty extends Model
{
    use SoftDeletes;

    protected $table = 'clinic_specialty';

    protected $fillable = [
        'clinic_id',
        'specialty_id',
    ];
}
