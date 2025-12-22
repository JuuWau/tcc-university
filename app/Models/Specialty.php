<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    protected $fillable = ['name'];

    public function periods()
    {
        return $this->belongsToMany(
            Period::class,
            'period_specialty'
        )->withTimestamps();
    }
}
