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
        $university = University::factory()->create([
            'name' => 'Universidade Teste',
            'slug' => 'universidade-teste',
            'cnpj' => '12.345.678/0001-90',
            'email' => 'teste@universidade.com',
            'phone' => '(11) 1234-5678',
            'cellphone' => '(11) 91234-5678',
        ]);

        Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );

        Role::firstOrCreate(
            ['slug' => 'student'],
            ['name' => 'Student']
        );

        User::factory()->create([
            'university_id' => $university->id,
            'role_id' => Role::where('slug', 'admin')->first()->id,
            'email' => 'juliawauters04@gmail.com',
            'password' => bcrypt('123'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null
        ]);

        $this->call([
            StudentSeeder::class,
        ]);
    }
}
