<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrePatient extends Model
{
    public const STATUS_WAITING = 'waiting';

    public const STATUS_CONVERTED  = 'converted';

    public const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'university_id',
        'name',
        'cpf',
        'birth_date',
        'biological_sex',
        'phone',
        'email',
        'status',
        'patient_type'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_WAITING,
            self::STATUS_CONVERTED,
            self::STATUS_CANCELLED,
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function waitingLists(): HasMany
    {
        return $this->hasMany(ClinicWaitingList::class);
    }

    public function prePatientClinics(): HasMany
    {
        return $this->hasMany(PrePatientClinic::class);
    }
}