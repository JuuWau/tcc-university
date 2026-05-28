<?php

namespace App\Imports;

use App\Models\PatientImport as PatientImportModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterImport;

class PatientsImport implements
    WithMultipleSheets,
    WithEvents,
    WithChunkReading,
    ShouldQueue
{
    use RemembersRowNumber;
    
    public function __construct(
        protected PatientImportModel $importLog,
        protected int $universityId,
        protected array $sheetNames,
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->sheetNames as $sheetName) {
            $sheets[$sheetName] = new PatientsSheetImport(
                $this->importLog,
                $this->universityId,
                $sheetName,
            );
        }

        return $sheets;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                $this->importLog->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}