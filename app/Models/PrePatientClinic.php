<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrePatientClinic extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'pre_patient_id',
        'clinic_id',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    public function prePatient(): BelongsTo
    {
        return $this->belongsTo(PrePatient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
