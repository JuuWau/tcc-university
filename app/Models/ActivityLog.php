<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const SPECIALTIES = 'Especialidades';
    public const PERIODS = 'Periodos';
    public const PATIENTS = 'Pacientes';
    public const CLINICS = 'Clínicas';

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'model_type',
        'model_id',
        'changes',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
