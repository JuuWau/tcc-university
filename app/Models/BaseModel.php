<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BaseModel extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('university', function (Builder $builder) {
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();

                $builder->where(
                    $builder->getModel()->getTable() . '.university_id',
                    $user->university_id
                );
            }
        });
    }
}
