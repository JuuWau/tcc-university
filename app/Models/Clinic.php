<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Clinic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'university_id',
        'name',
        'active',
        'specialty_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scheduleSlots()
    {
        return $this->hasMany(ScheduleSlot::class);
    }

    public function waitingList(): HasMany
    {
        return $this->hasMany(ClinicWaitingList::class);
    }

    public function patientClinics(): HasMany
    {
        return $this->hasMany(PatientClinic::class);
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(
            Patient::class,
            'patient_clinics'
        )->withPivot('enrolled_at');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }
}
