<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodSpecialty extends Model
{
    use SoftDeletes;

    protected $table = 'period_specialty';

    protected $fillable = [
        'period_id',
        'specialty_id',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public $timestamps = true;
}
