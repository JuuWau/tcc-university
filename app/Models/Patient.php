<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Patient extends Model
{
    use SoftDeletes;

    public const STATUS_ATIVO = 'ativo';

    public const STATUS_INATIVO = 'inativo';

    public const STATUS_TRATAMENTO = 'tratamento';

    public const STATUS_PAUSA_TRATAMENTO = 'pausa_tratamento';

    public const STATUS_ABANDONO = 'abandono';

    public const STATUS_CONCLUIDO = 'concluido';

    public const STATUS_TRANSFERENCIA = 'transferencia';

    public static function statuses(): array
    {
        return [
            self::STATUS_ATIVO,
            self::STATUS_INATIVO,
            self::STATUS_TRATAMENTO,
            self::STATUS_PAUSA_TRATAMENTO,
            self::STATUS_ABANDONO,
            self::STATUS_CONCLUIDO,
            self::STATUS_TRANSFERENCIA,
        ];
    }

    protected $fillable = [
        'university_id',
        'student_id',
        'name',
        'cpf',
        'birth_date',
        'phone',
        'email',
        'status',
        'code',
        'patient_type',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function patientStudents()
    {
        return $this->hasMany(PatientStudent::class);
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'patient_students'
        )->withTimestamps()
        ->wherePivotNull('deleted_at');
    }
    
    public function waitingLists(): HasMany
    {
        return $this->hasMany(ClinicWaitingList::class);
    }

    public function patientClinics(): HasMany
    {
        return $this->hasMany(PatientClinic::class);
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(
            Clinic::class,
            'patient_clinics'
        )->withPivot('enrolled_at');
    }
}
