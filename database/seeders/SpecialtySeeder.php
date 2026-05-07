<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\University;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $university = University::first();
        if (! $university) {
            $this->command?->warn('Nenhuma universidade encontrada para seed de especialidades.');
            return;
        }

        $specialties = [
            'Clínica Geral',
            'Dentística',
            'Endodontia',
            'Periodontia',
            'Prótese',
            'Cirurgia Oral',
            'Odontopediatria',
            'Ortodontia',
        ];

        foreach ($specialties as $name) {
            Specialty::firstOrCreate([
                'university_id' => $university->id,
                'name' => $name,
            ]);
        }
    }
}
