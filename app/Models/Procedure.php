<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'university_id',
        'specialty_id',
        'name',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
