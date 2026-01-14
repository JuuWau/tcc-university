<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class University extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function people()
    {
        return $this->hasMany(Person::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
