<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Specialty;
use App\Models\University;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::first();
        if (! $university) {
            $this->command?->warn('Nenhuma universidade encontrada para seed de períodos.');
            return;
        }

        $periodsData = [
            ['academic_year' => 4, 'semester' => 1, 'calendar_year' => 2026],
            ['academic_year' => 4, 'semester' => 2, 'calendar_year' => 2026],
            ['academic_year' => 5, 'semester' => 1, 'calendar_year' => 2026],
            ['academic_year' => 5, 'semester' => 2, 'calendar_year' => 2026],
        ];

        $specialtyIds = Specialty::query()
            ->where('university_id', $university->id)
            ->pluck('id')
            ->all();

        foreach ($periodsData as $data) {
            $period = Period::firstOrCreate([
                'university_id' => $university->id,
                'academic_year' => $data['academic_year'],
                'semester' => $data['semester'],
                'calendar_year' => $data['calendar_year'],
            ]);

            if ($specialtyIds) {
                $period->specialties()->syncWithoutDetaching($specialtyIds);
            }
        }
    }
}
