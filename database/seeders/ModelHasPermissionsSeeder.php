<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModelHasPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::findOrFail(1);

        $permissions = Permission::all();

        $admin->syncPermissions($permissions);
    }
}
