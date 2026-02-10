<?php

namespace Database\Seeders;

use App\Models\StudentReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            StudentReason::LEAVE_OF_ABSENCE,
            StudentReason::TRANSFER,
            StudentReason::WITHDRAWAL,
            StudentReason::GRADUATION,
            StudentReason::DELINQUENCY,
            StudentReason::ADMINISTRATIVE,
            StudentReason::ADMINISTRATIVE_CORRECTION,
            StudentReason::RETURNED_FROM_LEAVE,
            StudentReason::OTHER,
        ];

        foreach ($reasons as $reason) {
            StudentReason::firstOrCreate([
                'type' => $reason,
            ]);
        }
    }
}
