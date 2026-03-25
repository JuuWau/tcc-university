<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'university_id',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scheduleSlots()
    {
        return $this->hasMany(ScheduleSlot::class);
    }
}
