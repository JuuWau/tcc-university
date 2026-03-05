<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria o usuário admin e seu registro em people (nome).
     */
    public function run(): void
    {
        $university = University::first();
        if (!$university) {
            $this->command->warn('Nenhuma universidade encontrada. Execute o seed da universidade antes.');

            return;
        }

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );

        $user = User::firstOrCreate(
            ['email' => 'juliawauters04@gmail.com'],
            [
                'university_id' => $university->id,
                'role_id' => $adminRole->id,
                'password' => Hash::make('123'),
                'email_verified_at' => now(),
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]
        );

        Person::firstOrCreate(
            ['user_id' => $user->id],
            [
                'university_id' => $university->id,
                'name' => 'Administrador',
            ]
        );
    }
}
