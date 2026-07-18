<?php

namespace App\Services;

use App\Data\Patients\PatientTableFiltersData;
use App\Models\Address;
use App\Models\Patient;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientService
{
        /**
         * @param  'all'|'ativo'|'inativo'|'tratamento'|'pausa_tratamento'|'abandono'|'concluido'|'transferencia'  $status
         * @param  'name'|'email'|'created_at'  $sortField
         * @param  'asc'|'desc'  $sortDir
         */
        public function paginate(PatientTableFiltersData $filters): LengthAwarePaginator
        {
                $query = Patient::query()
                        ->with(['students.person', 'address'])
                        ->when(
                                $filters->universityId,
                                fn($q) => $q->where('university_id', $filters->universityId)
                        );

                $query->when($filters->search, function ($query) use ($filters) {
                        $query->where(function ($q) use ($filters) {
                                $q->where('patients.name', 'ilike', "%{$filters->search}%")
                                        ->orWhere('patients.email', 'ilike', "%{$filters->search}%")
                                        ->orWhere('patients.phone', 'ilike', "%{$filters->search}%")
                                        ->orWhere('patients.code', 'ilike', "%{$filters->search}%");
                        });
                });

                $query->whereNull('patients.deleted_at');
                if ($filters->status !== 'all' && in_array($filters->status, Patient::statuses(), true)) {
                        $query->where('patients.status', $filters->status);
                }

                if ($filters->sortField === 'name') {
                        $query->orderBy('name', $filters->sortDir);
                } elseif ($filters->sortField === 'email') {
                        $query->orderBy('email', $filters->sortDir);
                } else {
                        $query->orderBy('created_at', $filters->sortDir)
                                ->orderBy('id', $filters->sortDir);
                }

                return $query->paginate($filters->perPage, ['*'], 'page', $filters->page);
        }

        public function find(int $id, ?int $universityId = null): Patient
        {
                return Patient::withTrashed()
                        ->with(['students.person', 'address'])
                        ->when(
                                $universityId,
                                fn($q) => $q->where('university_id', $universityId)
                        )
                        ->findOrFail($id);
        }

        public function create(array $data, int $universityId): Patient
        {
                if (! $universityId) {
                        throw new \RuntimeException('Universidade inválida');
                }

                return DB::transaction(function () use ($data, $universityId) {

                        $patient = Patient::create([
                                'university_id' => $universityId,
                                'name' => $data['name'],
                                'cpf' => $data['cpf'] ?? null,
                                'birth_date' => $data['birth_date'] ?? null,
                                'phone' => $data['phone'] ?? null,
                                'email' => $data['email'] ?? null,
                                'status' => $data['status'] ?? Patient::STATUS_ATIVO,
                                'code' => $data['code'],
                                'patient_type' => $data['patient_type'],
                        ]);

                        if (! empty($data['student_ids'])) {

                                $patient->students()->attach(
                                        collect($data['student_ids'])
                                                ->mapWithKeys(fn($id) => [
                                                        $id => [
                                                                'created_at' => now(),
                                                                'updated_at' => now(),
                                                        ]
                                                ])
                                                ->toArray()
                                );
                        }

                        $addressData = [
                                'cep' => $data['cep'] ?? null,
                                'street' => $data['street'] ?? null,
                                'number' => $data['number'] ?? null,
                                'neighborhood' => $data['neighborhood'] ?? null,
                                'city' => $data['city'] ?? null,
                                'state' => $data['state'] ?? null,
                                'complement' => $data['complement'] ?? null,
                        ];

                        $hasAddress = collect($addressData)->filter()->isNotEmpty();

                        if ($hasAddress) {
                                $patient->address()->create($addressData);
                        }

                        return $patient->load([
                                'students.person',
                                'address',
                        ]);
                });
        }

        public function update(int $id, array $data, ?int $universityId = null): Patient
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                DB::transaction(function () use ($patient, $data) {
                        $update = [
                                'name' => $data['name'] ?? $patient->name,
                                'email' => $data['email'] ?? $patient->email,
                                'phone' => $data['phone'] ?? $patient->phone,
                                'cpf' => $data['cpf'] ?? $patient->cpf,
                                'birth_date' => isset($data['birth_date']) ? $data['birth_date'] : $patient->birth_date,
                                'patient_type' => $data['patient_type'] ?? $patient->patient_type,
                        ];
                        if (array_key_exists('status', $data) && in_array($data['status'], Patient::statuses(), true)) {
                                $update['status'] = $data['status'];
                        }
                        $patient->update($update);

                        $addressData = [
                                'cep' => $data['cep'] ?? null,
                                'street' => $data['street'] ?? null,
                                'number' => $data['number'] ?? null,
                                'neighborhood' => $data['neighborhood'] ?? null,
                                'city' => $data['city'] ?? null,
                                'state' => $data['state'] ?? null,
                                'complement' => $data['complement'] ?? null,
                        ];

                        if ($patient->address) {
                                $patient->address->update($addressData);
                        } else {
                                $patient->address()->create($addressData);
                        }
                });

                return $patient->fresh([
                        'students.person',
                        'address',
                ]);
        }

        public function updateStudent(int $id, ?int $studentId, ?int $universityId = null): Patient
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                $patient->update(['student_id' => $studentId]);

                return $patient->fresh([
                        'students.person',
                        'address',
                ]);
        }

        public function deactivate(int $id, ?int $universityId = null): void
        {
                $patient = Patient::when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                $patient->update(['status' => Patient::STATUS_INATIVO]);
        }

        public function activate(int $id, ?int $universityId = null): void
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                $patient->update(['status' => Patient::STATUS_ATIVO]);
                if ($patient->trashed()) {
                        $patient->restore();
                }
        }

        public function updateStudentData(int $id, array $studentIds, string $status, string $code, ?int $universityId = null): Patient
        {
                if (! in_array($status, Patient::statuses(), true)) {
                        throw new \InvalidArgumentException('Status inválido');
                }

                $patient = Patient::withTrashed()
                        ->when(
                                $universityId,
                                fn($q) => $q->where('university_id', $universityId)
                        )
                        ->findOrFail($id);

                DB::transaction(function () use (
                        $patient,
                        $studentIds,
                        $status,
                        $code
                ) {

                        $patient->update([
                                'status' => $status,
                                'code' => $code,
                        ]);

                        $currentIds = $patient->students()
                                ->pluck('students.id')
                                ->toArray();

                        $toRemove = array_diff($currentIds, $studentIds);
                        $toAdd = array_diff($studentIds, $currentIds);

                        if (! empty($toRemove)) {
                                DB::table('patient_students')
                                        ->where('patient_id', $patient->id)
                                        ->whereIn('student_id', $toRemove)
                                        ->update([
                                                'deleted_at' => now(),
                                                'updated_at' => now(),
                                        ]);
                        }

                        foreach ($toAdd as $studentId) {

                                $existing = DB::table('patient_students')
                                        ->where('patient_id', $patient->id)
                                        ->where('student_id', $studentId)
                                        ->first();

                                if ($existing) {

                                        DB::table('patient_students')
                                                ->where('patient_id', $patient->id)
                                                ->where('student_id', $studentId)
                                                ->update([
                                                        'deleted_at' => null,
                                                        'updated_at' => now(),
                                                ]);
                                } else {

                                        DB::table('patient_students')
                                                ->insert([
                                                        'patient_id' => $patient->id,
                                                        'student_id' => $studentId,
                                                        'created_at' => now(),
                                                        'updated_at' => now(),
                                                ]);
                                }
                        }
                });

                return $patient->fresh([
                        'students.person',
                        'address',
                ]);
        }

        public function destroy(int $id, ?int $universityId = null): void
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                $patient->forceDelete();
        }
}
