<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientImport extends Model
{
    protected $fillable = [
        'user_id',
        'file',
        'total',
        'imported',
        'failed',
        'errors',
        'status',
    ];

    protected $casts = [
        'errors' => 'array',
    ];
}
