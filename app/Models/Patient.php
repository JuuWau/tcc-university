<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'university_id',
        'name',
        'cpf',
        'birth_date',
        'phone',
        'email',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
