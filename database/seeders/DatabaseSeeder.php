<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\University;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        University::firstOrCreate(
            ['slug' => 'universidade-teste'],
            [
                'name' => 'Universidade Teste',
                'cnpj' => '12.345.678/0001-90',
                'email' => 'teste@universidade.com',
                'phone' => '(11) 1234-5678',
                'cellphone' => '(11) 91234-5678',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin'],
            ['guard_name' => 'web']
        );

        Role::firstOrCreate(
            ['slug' => 'student'],
            ['name' => 'Student'],
            ['guard_name' => 'web']
        );

        Role::firstOrCreate(
            ['slug' => 'recepcionist'],
            ['name' => 'Recepção'],
            ['guard_name' => 'web']
        );

        Role::firstOrCreate(
            ['slug' => 'professor'],
            ['name' => 'Professor'],
            ['guard_name' => 'web']
        );

        $this->call([
            SpecialtySeeder::class,
            PeriodSeeder::class,
            AdminUserSeeder::class,
            StudentSeeder::class,
            PermissionSeeder::class,
            ModelHasPermissionsSeeder::class,
            ModelHasRolesSeeder::class,
        ]);
    }
}
