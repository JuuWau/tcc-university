<?php

namespace Database\Seeders;

use App\Models\University;
use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        University::factory()->create([
            'name' => 'Universidade Teste',
            'slug' => 'universidade-teste',
            'cnpj' => '12.345.678/0001-90',
            'email' => 'teste@universidade.com',
            'phone' => '(11) 1234-5678',
            'cellphone' => '(11) 91234-5678',
        ]);

        Role::factory()->create([
            'name' => 'Admin',
            'name' => 'admin',
        ]);

        User::factory()->create([
            'university_id' => 1,
            'role_id' => 1,
            'email' => 'juliawauters04@gmail.com',
            'password' => bcrypt('123'),
        ]);
    }
}
