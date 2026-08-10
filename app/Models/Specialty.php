<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'university_id',
    ];

    public function periods()
    {
        return $this->belongsToMany(
            Period::class,
            'period_specialty'
        )->withTimestamps();
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }
}
