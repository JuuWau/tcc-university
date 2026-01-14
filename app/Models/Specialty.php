<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

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
}
