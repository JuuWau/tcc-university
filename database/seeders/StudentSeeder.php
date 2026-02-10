<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $universityId = 1;

        $studentRole = Role::where('slug', 'student')->firstOrFail();

        for ($i = 1; $i <= 10; $i++) {

            $user = User::create([
                'email' => "aluno{$i}@teste.com",
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'university_id' => $universityId,
            ]);

            $person = Person::create([
                'user_id' => $user->id,
                'university_id' => $universityId,
                'name' => "Aluno {$i}",
                'cpf' => $this->fakeCpf($i),
                'birth_date' => now()->subYears(rand(18, 30)),
                'phone' => '1199' . rand(1000000, 9999999),
            ]);

            $person->address()->create([
                'cep' => '01001-000',
                'street' => 'Rua Exemplo',
                'number' => (string) rand(10, 999),
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'complement' => null,
            ]);

            Student::create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'university_id' => $universityId,
                'registration' => 'REG' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    private function fakeCpf(int $seed): string
    {
        return str_pad((string) ($seed * 111111111), 11, '0', STR_PAD_LEFT);
    }
}
