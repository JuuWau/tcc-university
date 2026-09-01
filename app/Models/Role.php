<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    public const ADMIN = 1;
    public const STUDENT = 2;
    public const RECEPTIONIST = 3;
    public const PROFESSOR = 4;

    protected $fillable = [
        'name',
        'slug',
        'guard_name',
    ];
}
