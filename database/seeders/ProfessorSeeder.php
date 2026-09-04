<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::first();

        if (!$university) {
            $this->command->warn(
                'Nenhuma universidade encontrada. Execute o seed da universidade antes.'
            );

            return;
        }

        $user = User::firstOrCreate(
            ['email' => 'professor@acadent.com.br'],
            [
                'university_id' => $university->id,
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
                'name' => 'Professor',
            ]
        );
    }
}
