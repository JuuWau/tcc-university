<?php

namespace App\Imports;

use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class PatientsSheetImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    use RemembersChunkOffset;
    
    protected array $errors = [];

    public function __construct(
        protected $importLog,
        protected int $universityId,
        protected string $sheetName = 'unknown'
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {

            $excelRowNumber = $this->getChunkOffset() + $index;

            $row = collect($row)->mapWithKeys(function ($value, $key) {
                $key = Str::of((string) $key)
                    ->ascii()
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]/', '_')
                    ->toString();

                return [$key => is_string($value) ? trim($value) : $value];
            });

            $name = trim((string) $row->get('pacientes'));
            $code = $this->normalizeCode($row->get('codigo'));

            if ($name === '' && $code === '') {
                $this->logError($excelRowNumber, 'linha vazia');
                continue;
            }

            if ($name === '') {
                $this->logError($excelRowNumber, "código {$code} sem nome");
                continue;
            }

            if ($code === '') {
                $this->logError($excelRowNumber, "paciente {$name} sem código");
                continue;
            }

            $validator = Validator::make($row->toArray(), [
                'codigo' => ['required'],
                'pacientes' => ['required'],
            ]);

            if ($validator->fails()) {
                $this->logError(
                    $excelRowNumber,
                    $validator->errors()->first(),
                    $name
                );
                continue;
            }

            try {
                $exists = Patient::where('code', $code)
                    ->where('university_id', $this->universityId)
                    ->exists();

                if ($exists) {
                    $this->logError($excelRowNumber, "já cadastrado {$name}");
                    continue;
                }

                app(PatientService::class)->create([
                    'code' => $code,
                    'name' => $name,
                    'phone' => (string) $row->get('telefone', ''),
                    'patient_type' => Str::lower($row->get('clinica')) === 'adulto'
                        ? 'adulto'
                        : 'pediatria',
                    'status' => Patient::STATUS_ATIVO,
                    'student_ids' => [],
                ], $this->universityId);

                $this->importLog->increment('imported');

            } catch (Throwable $e) {
                $this->logError($excelRowNumber, $e->getMessage(), $name);
            }
        }

        $this->flushErrors();
    }

    private function logError(int $row, string $message, ?string $name = null): void
    {
        $this->errors[] = [
            'sheet' => $this->sheetName,
            'row' => $row,
            'patient' => $name,
            'message' => $message,
        ];

        $this->importLog->increment('failed');
    }

    private function flushErrors(): void
    {
        $current = $this->importLog->fresh()->errors ?? [];

        $this->importLog->update([
            'errors' => array_merge($current, $this->errors),
        ]);

        $this->errors = [];
    }

    private function normalizeCode(?string $code): string
    {
        $code = trim((string) $code);

        return strtoupper(str_replace(["–", "—", "−"], "-", $code));
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headingRow(): int
    {
        return 1;
    }
}