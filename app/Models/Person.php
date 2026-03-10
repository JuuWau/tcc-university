<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'university_id',
        'cpf',
        'birth_date',
        'phone',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
