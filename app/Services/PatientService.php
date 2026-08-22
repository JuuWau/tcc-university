<?php

namespace App\Services;

use App\Constants\ActivityLogPrefixes;
use App\Constants\ActivityModules;
use App\Data\Patients\PatientClinicsTableFiltersData;
use App\Data\Patients\PatientTableFiltersData;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicWaitingList;
use App\Models\Patient;
use App\Models\PatientClinic;
use App\Models\Period;
use App\Models\ScheduleSlot;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as Collec;
use Carbon\Carbon;

class PatientService
{
        /**
         * @param  'all'|'ativo'|'inativo'|'tratamento'|'pausa_tratamento'|'abandono'|'concluido'|'transferencia'  $status
         * @param  'name'|'email'|'created_at'  $sortField
         * @param  'asc'|'desc'  $sortDir
         */
        public function paginate(PatientTableFiltersData $filters, User $user): LengthAwarePaginator
        {
                $query = Patient::query()
                        ->with(['students.person', 'address'])
                        ->when(
                                $filters->universityId,
                                fn($q) => $q->where('university_id', $filters->universityId)
                        );

                if ($user->hasRole('student')) {
                        $query->whereHas('students', function ($q) use ($user) {
                                $q->where('students.id', $user->student->id);
                        });
                }

                $query->when($filters->search, function ($query) use ($filters) {
                        $query->where(function ($q) use ($filters) {
                                $q->where('patients.name', 'ilike', "%{$filters->search}%")
                                        ->orWhere('patients.email', 'ilike', "%{$filters->search}%")
                                        ->orWhere('patients.phone', 'ilike', "%{$filters->search}%")
                                        ->orWhere('patients.code', 'ilike', "%{$filters->search}%");
                        });
                });

                $query->whereNull('patients.deleted_at');

                if (
                        $filters->status !== 'all' &&
                        in_array($filters->status, Patient::statuses(), true)
                ) {
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

                return $query->paginate(
                        $filters->perPage,
                        ['*'],
                        'page',
                        $filters->page
                );
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
                                'biological_sex' => $data['biological_sex']
                        ]);

                        $changes = ActivityLogService::getCreatedChanges($patient);

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

                                ActivityLogService::trackRelationChanges(
                                        $changes,
                                        ActivityLogPrefixes::STUDENT,
                                        [],
                                        ActivityLogService::getRelationValues(
                                                Student::class,
                                                $data['student_ids'],
                                                fn(Student $student) => $student->person->name,
                                        ),
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
                                $address = $patient->address()->create($addressData);

                                $changes = array_merge(
                                        $changes,
                                        ActivityLogService::getCreatedChanges(
                                                $address,
                                                ActivityLogPrefixes::ADDRESS,
                                        ),
                                );
                        }

                        ActivityLogService::created(
                                ActivityModules::PATIENTS,
                                "Cadastrou o paciente '{$patient->name}'.",
                                $patient,
                                $changes,
                        );

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
                        $patient->fill([
                                'name' => $data['name'] ?? $patient->name,
                                'email' => $data['email'] ?? $patient->email,
                                'phone' => $data['phone'] ?? $patient->phone,
                                'cpf' => $data['cpf'] ?? $patient->cpf,
                                'birth_date' => $data['birth_date'] ?? $patient->birth_date,
                                'patient_type' => $data['patient_type'] ?? $patient->patient_type,
                                'status' => array_key_exists('status', $data)
                                        && in_array($data['status'], Patient::statuses(), true)
                                        ? $data['status']
                                        : $patient->status,
                                'biological_sex' => $data['biological_sex'],
                        ]);

                        $changes = ActivityLogService::getChanges($patient);

                        $address = $patient->address()->firstOrNew();

                        $address->fill([
                                'cep' => $data['cep'] ?? null,
                                'street' => $data['street'] ?? null,
                                'number' => $data['number'] ?? null,
                                'neighborhood' => $data['neighborhood'] ?? null,
                                'city' => $data['city'] ?? null,
                                'state' => $data['state'] ?? null,
                                'complement' => $data['complement'] ?? null,
                        ]);

                        $changes = array_merge(
                                $changes,
                                $address->exists
                                        ? ActivityLogService::getChanges(
                                                $address,
                                                ActivityLogPrefixes::ADDRESS,
                                        )
                                        : ActivityLogService::getCreatedChanges(
                                                $address,
                                                ActivityLogPrefixes::ADDRESS,
                                        ),
                        );

                        if (empty($changes)) {
                                return;
                        }

                        $patient->save();
                        $address->save();

                        ActivityLogService::updated(
                                ActivityModules::PATIENTS,
                                "Atualizou o paciente '{$patient->name}'.",
                                $patient,
                                $changes,
                        );
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

                DB::transaction(function () use ($patient, $studentId) {
                        $changes = [];

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                ActivityLogPrefixes::STUDENT,
                                $patient->students
                                        ->map(fn(Student $student) => $student->person->name)
                                        ->sort()
                                        ->values()
                                        ->toArray(),
                                $studentId
                                        ? ActivityLogService::getRelationValues(
                                                Student::class,
                                                [$studentId],
                                                fn(Student $student) => $student->person->name,
                                        )
                                        : [],
                        );

                        if (empty($changes)) {
                                return;
                        }

                        $patient->students()->sync(
                                $studentId ? [$studentId] : []
                        );

                        ActivityLogService::updated(
                                ActivityModules::PATIENTS,
                                "Atualizou o(s) aluno(s) do paciente '{$patient->name}'.",
                                $patient,
                                $changes,
                        );
                });

                return $patient->fresh([
                        'students.person',
                        'address',
                ]);
        }

        public function deactivate(int $id, ?int $universityId = null): void
        {
                $patient = Patient::when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                DB::transaction(function () use ($patient) {
                        $patient->fill([
                                'status' => Patient::STATUS_INATIVO,
                        ]);

                        $changes = ActivityLogService::getChanges($patient);

                        if (empty($changes)) {
                                return;
                        }

                        $patient->save();

                        ActivityLogService::updated(
                                ActivityModules::PATIENTS,
                                "Inativou o paciente '{$patient->name}'.",
                                $patient,
                                $changes,
                        );
                });
        }

        public function activate(int $id, ?int $universityId = null): void
        {
                $patient = Patient::withTrashed()
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId))
                        ->findOrFail($id);

                DB::transaction(function () use ($patient) {
                        $patient->fill([
                                'status' => Patient::STATUS_ATIVO,
                        ]);

                        $changes = ActivityLogService::getChanges($patient);

                        if ($patient->trashed()) {
                                $patient->restore();
                        }

                        if (empty($changes)) {
                                return;
                        }

                        $patient->save();

                        ActivityLogService::updated(
                                ActivityModules::PATIENTS,
                                "Ativou o paciente '{$patient->name}'.",
                                $patient,
                                $changes,
                        );
                });
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
                        $patient->fill([
                                'status' => $status,
                                'code' => $code,
                        ]);

                        $changes = ActivityLogService::getChanges($patient);

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                ActivityLogPrefixes::STUDENT,
                                $patient->students
                                        ->map(fn(Student $student) => $student->person->name)
                                        ->sort()
                                        ->values()
                                        ->toArray(),
                                ActivityLogService::getRelationValues(
                                        Student::class,
                                        $studentIds,
                                        fn(Student $student) => $student->person->name,
                                ),
                        );

                        if (empty($changes)) {
                                return;
                        }

                        $patient->save();

                        $currentIds = $patient->students()
                                ->pluck('students.id')
                                ->toArray();

                        $toRemove = array_diff($currentIds, $studentIds);
                        $toAdd = array_diff($studentIds, $currentIds);

                        if (!empty($toRemove)) {
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

                        ActivityLogService::updated(
                                ActivityModules::PATIENTS,
                                "Atualizou os dados acadêmicos do paciente '{$patient->name}'.",
                                $patient,
                                $changes,
                        );
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

        public function availableForClinic(Clinic $clinic)
        {
                return Patient::query()
                        ->whereDoesntHave('waitingLists', function ($query) use ($clinic) {
                                $query->where('clinic_id', $clinic->id);
                        })
                        ->whereDoesntHave('patientClinics', function ($query) use ($clinic) {
                                $query->where('clinic_id', $clinic->id);
                        })
                        ->orderBy('name')
                        ->get();
        }

        public function paginateClinics(Patient $patient, PatientClinicsTableFiltersData $filters): LengthAwarePaginator
        {

                if ($filters->status === 'waiting') {
                        $query = ClinicWaitingList::query()
                                ->with('clinic');
                } else {
                        $query = PatientClinic::query()
                                ->with('clinic');
                }

                $query->where(
                        'patient_id',
                        $patient->id
                );

                $query->orderBy(
                        'created_at',
                        'asc'
                );

                return $query->paginate(
                        $filters->perPage,
                        ['*'],
                        'page',
                        $filters->page
                );
        }

        public function removeEnrollment(Patient $patient, Clinic $clinic): void
        {
                DB::transaction(function () use ($patient, $clinic) {
                        $enrollment = PatientClinic::query()
                                ->where('patient_id', $patient->id)
                                ->where('clinic_id', $clinic->id)
                                ->firstOrFail();

                        $changes = [];

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                ActivityLogPrefixes::CLINIC,
                                [
                                        $clinic->name,
                                ],
                                [],
                        );

                        ActivityLogService::deleted(
                                ActivityModules::PATIENTS,
                                "Removeu a matrícula do paciente {$patient->code} - {$patient->name} da clínica '{$clinic->name}'.",
                                $patient,
                                $changes,
                        );

                        $enrollment->delete();
                });
        }

        public function enrollClinic(Clinic $clinic, int $patientId): PatientClinic
        {
                return DB::transaction(function () use ($clinic, $patientId) {
                        if (
                                PatientClinic::where('clinic_id', $clinic->id)
                                ->where('patient_id', $patientId)
                                ->exists()
                        ) {
                                throw new \Exception(
                                        'Paciente já está inscrito nesta clínica.'
                                );
                        }

                        $patientClinic = PatientClinic::create([
                                'clinic_id' => $clinic->id,
                                'patient_id' => $patientId,
                                'enrolled_at' => now(),
                        ]);

                        ClinicWaitingList::where('clinic_id', $clinic->id)
                                ->where('patient_id', $patientId)
                                ->delete();

                        $patient = Patient::findOrFail($patientId);

                        $changes = ActivityLogService::getCreatedChanges($patientClinic);

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                ActivityLogPrefixes::CLINIC,
                                [],
                                [$clinic->name],
                        );

                        ActivityLogService::created(
                                ActivityModules::PATIENTS,
                                "Inscreveu o paciente {$patient->code} - {$patient->name} na clínica '{$clinic->name}'.",
                                $patient,
                                $changes,
                        );

                        return $patientClinic;
                });
        }

        public function addToWaitingList(Clinic $clinic, array $data): ClinicWaitingList
        {
                return DB::transaction(function () use ($clinic, $data) {
                        if (
                                ClinicWaitingList::query()
                                ->where('clinic_id', $clinic->id)
                                ->where('patient_id', $data['patient_id'])
                                ->exists()
                        ) {
                                throw new \Exception(
                                        'Paciente já está na lista de espera desta clínica.'
                                );
                        }

                        if (
                                PatientClinic::query()
                                ->where('clinic_id', $clinic->id)
                                ->where('patient_id', $data['patient_id'])
                                ->exists()
                        ) {
                                throw new \Exception(
                                        'Paciente já está inscrito nesta clínica.'
                                );
                        }

                        $waitingList = ClinicWaitingList::create([
                                'clinic_id' => $clinic->id,
                                'patient_id' => $data['patient_id'],
                                'enrolled_at' => now(),
                        ]);

                        $patient = Patient::findOrFail($data['patient_id']);

                        $changes = ActivityLogService::getCreatedChanges($waitingList);

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                ActivityLogPrefixes::CLINIC,
                                [],
                                [$clinic->name],
                        );

                        ActivityLogService::created(
                                ActivityModules::PATIENTS,
                                "Adicionou o paciente {$patient->code} - {$patient->name} à lista de espera da clínica '{$clinic->name}'.",
                                $patient,
                                $changes,
                        );

                        return $waitingList;
                });
        }

        public function availableClinics(Patient $patient): Collec
        {
                $enrolledClinicIds = PatientClinic::query()
                        ->where('patient_id', $patient->id)
                        ->pluck('clinic_id');

                $waitingClinicIds = ClinicWaitingList::query()
                        ->where('patient_id', $patient->id)
                        ->pluck('clinic_id');

                $blockedClinicIds = $enrolledClinicIds
                        ->merge($waitingClinicIds)
                        ->unique();

                return Clinic::query()
                        ->when(
                                $blockedClinicIds->isNotEmpty(),
                                fn($query) => $query->whereNotIn(
                                        'id',
                                        $blockedClinicIds
                                )
                        )
                        ->orderBy('name')
                        ->get()
                        ->map(fn(Clinic $clinic) => [
                                'label' => $clinic->name,
                                'value' => $clinic->id,
                        ]);
        }

        public function list(Patient $patient): array
        {
                $relations = [
                        'student.person',
                        'procedure',
                        'enrollment.slot.clinic',
                        'enrollment.slot.period',
                        'enrollment.slot.responsibles.person',
                ];

                $upcoming = Appointment::query()
                        ->with($relations)
                        ->where('patient_id', $patient->id)
                        ->where('scheduled_start_at', '>=', now())
                        ->orderBy('scheduled_start_at')
                        ->get();

                $completed = Appointment::query()
                        ->with($relations)
                        ->where('patient_id', $patient->id)
                        ->where('scheduled_start_at', '<', now())
                        ->orderByDesc('scheduled_start_at')
                        ->get();

                return [
                        'upcoming' => $upcoming,
                        'completed' => $completed,
                ];
        }

        public function getEnrolledClinics(Patient $patient): Collection
        {
                return $patient->clinics()
                        ->select([
                                'clinics.id',
                                'clinics.name',
                        ])
                        ->get();
        }

        public function getClinicPeriods(Clinic $clinic): Collection
        {
                return Period::query()
                        ->where('university_id', $clinic->university_id)
                        ->whereHas('scheduleSlots', function ($query) use ($clinic) {
                                $query->where('clinic_id', $clinic->id);
                        })
                        ->orderByDesc('calendar_year')
                        ->orderByDesc('academic_year')
                        ->orderByDesc('semester')
                        ->get();
        }

        public function getClinicStudents(Patient $patient, array $filters)
        {
                return Student::query()
                        ->with('currentPeriod', 'person')
                        ->whereHas('enrollments.slot', function ($query) use ($filters) {
                                $query
                                        ->where('clinic_id', $filters['clinic_id'])
                                        ->where('period_id', $filters['period_id']);
                        })
                        ->whereHas('currentPeriod', function ($query) use ($filters) {
                                $query->where('period_id', $filters['period_id']);
                        })
                        ->get();
        }

        public function getAvailableDays(Patient $patient, array $data): array
        {
                $slots = ScheduleSlot::query()
                        ->where('clinic_id', $data['clinic_id'])
                        ->where('period_id', $data['period_id'])
                        ->whereYear('date', $data['year'])
                        ->whereMonth('date', $data['month'])
                        ->orderBy('date')
                        ->get();

                return [
                        'available_days' => $slots
                                ->pluck('date')
                                ->map(fn($date) => $date->format('Y-m-d'))
                                ->unique()
                                ->values()
                                ->all(),
                ];
        }

        public function getAvailableTimes(Patient $patient, array $data): array 
        {
                $slot = ScheduleSlot::query()
                        ->where('clinic_id', $data['clinic_id'])
                        ->where('period_id', $data['period_id'])
                        ->whereDate('date', $data['date'])
                        ->first();

                if (!$slot) {
                        return [
                                'available_times' => [],
                        ];
                }

                $appointments = Appointment::query()
                        ->whereDate('scheduled_start_at', $data['date'])
                        ->whereIn('status', [
                                'scheduled',
                                'confirmed',
                        ])
                        ->get([
                                'scheduled_start_at',
                                'scheduled_end_at',
                        ]);

                $duration = (int) $data['duration'];

                $start = Carbon::parse(
                        $data['date'] . ' ' . $slot->start_time
                );

                $end = Carbon::parse(
                        $data['date'] . ' ' . $slot->end_time
                );

                $availableTimes = [];

                while ($start->copy()->addMinutes($duration)->lte($end)) {
                        $timeStart = $start->copy();
                        $timeEnd = $start->copy()->addMinutes($duration);

                        $hasConflict = $appointments->contains(function ($appointment) use (
                                $timeStart,
                                $timeEnd
                        ) {
                                $appointmentStart = Carbon::parse(
                                        $appointment->scheduled_start_at
                                );

                                $appointmentEnd = Carbon::parse(
                                        $appointment->scheduled_end_at
                                );

                                return $timeStart->lt($appointmentEnd)
                                        && $timeEnd->gt($appointmentStart);
                        });

                        if (!$hasConflict) {
                                $availableTimes[] = [
                                        'start_time' => $timeStart->format('H:i'),
                                        'end_time' => $timeEnd->format('H:i'),
                                ];
                        }

                        $start->addMinutes($duration);
                }

                return [
                        'available_times' => $availableTimes,
                ];
        }
}
