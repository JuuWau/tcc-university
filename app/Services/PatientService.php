<?php

namespace App\Services;

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
        public function paginate(
                int $page = 1,
                int $perPage = 15,
                string $sortField = 'created_at',
                string $sortDir = 'desc',
                string $status = 'all',
                ?int $universityId = null
        ): LengthAwarePaginator {
                $query = Patient::withTrashed()
                        ->with(['student.person', 'address'])
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId));

                $query->whereNull('patients.deleted_at');
                if ($status !== 'all' && in_array($status, Patient::statuses(), true)) {
                        $query->where('patients.status', $status);
                }

                if ($sortField === 'name') {
                        $query->orderBy('name', $sortDir);
                } elseif ($sortField === 'email') {
                        $query->orderBy('email', $sortDir);
                } else {
                        $query->orderBy('created_at', $sortDir);
                }

                return $query->paginate($perPage, ['patients.*'], 'page', $page);
        }

        public function find(int $id, ?int $universityId = null): array
        {
                $patient = Patient::withTrashed()
                        ->with(['student.person', 'address'])
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                return $this->formatForTab($patient);
        }

        public function create(array $data, int $universityId): Patient
        {
                if (! $universityId) {
                        throw new \RuntimeException('Universidade inválida');
                }

                return DB::transaction(function () use ($data, $universityId) {
                        $patient = Patient::create([
                                'university_id' => $universityId,
                                'student_id' => $data['student_id'] ?? null,
                                'name' => $data['name'],
                                'cpf' => $data['cpf'] ?? null,
                                'birth_date' => $data['birth_date'] ?? null,
                                'phone' => $data['phone'] ?? null,
                                'email' => $data['email'] ?? null,
                                'status' => $data['status'] ?? Patient::STATUS_ATIVO,
                        ]);

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

                        return $patient->load(['student.person', 'address']);
                });
        }

        public function update(int $id, array $data, ?int $universityId = null): array
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

                return $this->formatForTab($patient->fresh(['student.person', 'address']));
        }

        public function updateStudent(int $id, ?int $studentId, ?int $universityId = null): array
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                $patient->update(['student_id' => $studentId]);

                return $this->formatForTab($patient->fresh(['student.person', 'address']));
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

        public function updateStudentData(int $id, ?int $studentId, string $status, ?int $universityId = null): array
        {
                if (! in_array($status, Patient::statuses(), true)) {
                        throw new \InvalidArgumentException('Status inválido');
                }

                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                DB::transaction(function () use ($patient, $studentId, $status) {
                        $patient->update([
                                'student_id' => $studentId,
                                'status' => $status,
                        ]);
                });

                return $this->formatForTab($patient->fresh(['student.person', 'address']));
        }

        public function destroy(int $id, ?int $universityId = null): void
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                $patient->forceDelete();
        }

        /**
         * Format patient for table or tab (student as { id, name }).
         */
        public function formatForTab(Patient $patient): array
        {
                $student = null;
                if ($patient->relationLoaded('student') && $patient->student) {
                        $student = [
                                'id' => $patient->student->id,
                                'name' => $patient->student->person?->name ?? '—',
                        ];
                }

                $item = $patient->toArray();
                $item['student'] = $student;

                if ($patient->relationLoaded('address') && $patient->address) {
                        $item['address'] = $patient->address->toArray();
                }

                return $item;
        }

        /**
         * Format paginated items for table.
         */
        public function formatPaginatedItems(LengthAwarePaginator $paginator): array
        {
                $items = collect($paginator->items())->map(function (Patient $patient) {
                        return $this->formatForTab($patient);
                })->all();

                return $items;
        }
}
