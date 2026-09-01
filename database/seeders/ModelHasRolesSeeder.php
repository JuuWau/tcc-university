<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModelHasRolesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::findOrFail(1);

        $role = Role::findOrFail(Role::ADMIN);

        $admin->syncRoles([$role]);
    }
}
