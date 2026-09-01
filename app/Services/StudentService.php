<?php

namespace App\Services;

use App\Constants\ActivityLogPrefixes;
use App\Constants\ActivityModules;
use App\Data\Students\StudentTableFiltersData;
use App\Mail\UserInviteMail;
use App\Models\Address;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Period;
use App\Models\User;
use App\Models\Person;
use App\Models\Role;
use App\Models\ScheduleSlot;
use App\Models\Student;
use App\Models\UserActionLog;
use App\Models\UserInvite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Ramsey\Collection\Collection;

class StudentService
{
        public function all()
        {
                return Student::withTrashed()
                        ->with([
                                'person:id,name,cpf,phone',
                                'user:id,email',
                                'user.invite:id,user_id,used_at,expires_at,token',
                                'periods:id,academic_year,semester,calendar_year',
                        ])
                        ->orderBy('created_at', 'desc')
                        ->get();
        }

        /**
         * @param  'all'|'pending'|'active'|'inactive'  $status
         * @param  'person.name'|'registration'|'created_at'  $sortField
         * @param  'asc'|'desc'  $sortDir
         */
        public function paginate(StudentTableFiltersData $filters): LengthAwarePaginator
        {
                $query = Student::withTrashed()
                        ->with([
                                'person:id,name,cpf,phone',
                                'user:id,email',
                                'user.invite',
                                'periods:id,academic_year,semester,calendar_year',
                        ])
                        ->when(
                                $filters->universityId,
                                fn($q) => $q->where('university_id', $filters->universityId)
                        );

                $query->when($filters->search, function ($query) use ($filters) {
                        $query->where(function ($q) use ($filters) {

                                $q->whereHas('person', function ($person) use ($filters) {
                                        $person->where('name', 'ilike', "%{$filters->search}%")
                                                ->orWhere('phone', 'ilike', "%{$filters->search}%")
                                                ->orWhere('cpf', 'ilike', "%{$filters->search}%");
                                });

                                $q->orWhereHas('user', function ($user) use ($filters) {
                                        $user->where('email', 'ilike', "%{$filters->search}%");
                                });

                                $q->orWhere(
                                        'students.registration',
                                        'ilike',
                                        "%{$filters->search}%"
                                );
                        });
                });

                if ($filters->status === 'pending') {
                        $query->whereNull('students.deleted_at')
                                ->whereHas('user.invite', fn($q) => $q->whereNull('used_at'));
                } elseif ($filters->status === 'active') {
                        $query->whereNull('students.deleted_at')
                                ->where(function ($q) {
                                        $q->whereDoesntHave('user')
                                                ->orWhereDoesntHave('user.invite')
                                                ->orWhereHas('user.invite', fn($q) => $q->whereNotNull('used_at'));
                                });
                } elseif ($filters->status === 'inactive') {
                        $query->whereNotNull('students.deleted_at');
                }

                if ($filters->sortField === 'person.name') {
                        $query->join('people', 'students.person_id', '=', 'people.id')
                                ->orderBy('people.name', $filters->sortDir)
                                ->select('students.*');
                } elseif ($filters->sortField === 'registration') {
                        $query->orderBy('students.registration', $filters->sortDir);
                } else {
                        $query->orderBy('students.created_at', $filters->sortDir)
                                ->orderBy('students.id', $filters->sortDir);
                }

                return $query->paginate($filters->perPage, ['*'], 'page', $filters->page);
        }

        public function find(int $studentId): Student
        {
                return Student::withTrashed()
                        ->with([
                                'person:id,name,cpf,phone,birth_date',
                                'person.address',
                                'user:id,email',
                                'user.invite',
                                'periods:id,academic_year,semester,calendar_year',
                        ])
                        ->findOrFail($studentId);
        }

        public function update(int $studentId, array $data): Student
        {
                return DB::transaction(function () use ($studentId, $data) {
                        $student = Student::withTrashed()
                                ->with(['person', 'person.address', 'user'])
                                ->findOrFail($studentId);

                        $changes = [];

                        if ($student->user) {
                                $student->user->fill([
                                        'email' => $data['email'],
                                ]);

                                $changes = array_merge(
                                        $changes,
                                        ActivityLogService::getChanges($student->user, ActivityLogPrefixes::USER),
                                );

                                $student->user->save();

                                if (!empty($data['password'])) {
                                        $student->user->update([
                                                'password' => Hash::make($data['password']),
                                        ]);

                                        $changes['user.password'] = [
                                                'old' => '********',
                                                'new' => '********',
                                        ];
                                }
                        }

                        if ($student->person) {
                                $personData = [
                                        'cpf' => $data['cpf'],
                                        'phone' => $data['phone'],
                                        'birth_date' => $data['birth_date'],
                                ];

                                if (isset($data['name'])) {
                                        $personData['name'] = $data['name'];
                                }

                                $student->person->fill($personData);

                                $changes = array_merge(
                                        $changes,
                                        ActivityLogService::getChanges($student->person, ActivityLogPrefixes::PERSON),
                                );

                                $student->person->save();

                                $address = $student->person->address()->firstOrNew([
                                        'addressable_id' => $student->person->id,
                                        'addressable_type' => Person::class,
                                ]);

                                $address->fill([
                                        'cep' => $data['cep'],
                                        'street' => $data['street'],
                                        'number' => $data['number'],
                                        'neighborhood' => $data['neighborhood'],
                                        'city' => $data['city'],
                                        'state' => $data['state'],
                                        'complement' => $data['complement'] ?? null,
                                ]);

                                $changes = array_merge(
                                        $changes,
                                        ActivityLogService::getChanges($address, ActivityLogPrefixes::ADDRESS),
                                );

                                $address->save();
                        }

                        if (!empty($changes)) {
                                ActivityLogService::updated(
                                        ActivityModules::STUDENTS,
                                        "Atualizou o aluno '{$student->person->name}'.",
                                        $student,
                                        $changes,
                                );
                        }

                        return $student->fresh([
                                'person',
                                'person.address',
                                'user',
                                'user.invite',
                                'periods',
                        ]);
                });
        }

        public function updateAcademicData(int $studentId, array $data): Student
        {
                return DB::transaction(function () use ($studentId, $data) {
                        $student = Student::withTrashed()
                                ->with(['periods'])
                                ->findOrFail($studentId);

                        $student->fill([
                                'registration' => $data['registration'],
                        ]);

                        $changes = ActivityLogService::getChanges($student);

                        $currentPeriod = $student->periods
                                ->firstWhere('pivot.is_current', true);

                        $newPeriodId = (int) $data['period'];

                        ActivityLogService::trackBelongsToChange(
                                $changes,
                                'period_id',
                                ActivityLogPrefixes::PERIOD,
                                Period::class,
                                $currentPeriod?->id,
                                $newPeriodId,
                                fn(Period $period) =>
                                "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                        );

                        if (empty($changes)) {
                                return $student;
                        }

                        $student->save();

                        DB::table('student_periods')
                                ->where('student_id', $student->id)
                                ->update(['is_current' => false]);

                        if ($student->periods->contains('id', $newPeriodId)) {
                                $student->periods()->updateExistingPivot($newPeriodId, [
                                        'is_current' => true,
                                ]);
                        } else {
                                $student->periods()->attach($newPeriodId, [
                                        'started_at' => now(),
                                        'is_current' => true,
                                ]);
                        }

                        ActivityLogService::updated(
                                ActivityModules::STUDENTS,
                                "Atualizou os dados acadêmicos do aluno '{$student->person->name}'.",
                                $student,
                                $changes,
                        );

                        return $student->fresh([
                                'person',
                                'person.address',
                                'user',
                                'user.invite',
                                'periods',
                        ]);
                });
        }

        public function create(array $data, int $universityId): Student
        {
                return DB::transaction(function () use ($data, $universityId) {
                        $user = User::create([
                                'email' => $data['email'],
                                'university_id' => $universityId,
                                'password' => Hash::make(Str::random(32)),
                        ]);

                        $studentRole = Role::findOrFail(Role::STUDENT);

                        $user->assignRole($studentRole);

                        $person = Person::create([
                                'user_id' => null,
                                'name' => $data['name'],
                                'university_id' => $universityId,
                        ]);

                        $student = Student::create([
                                'person_id' => $person->id,
                                'user_id' => $user->id,
                                'registration' => $data['registration'],
                                'university_id' => $universityId,
                        ]);

                        $student->periods()->attach(
                                $data['period'],
                                ['started_at' => now()]
                        );

                        $changes = [];

                        ActivityLogService::trackRelationChanges(
                                $changes,
                                ActivityLogPrefixes::PERIOD,
                                [],
                                ActivityLogService::getRelationValues(
                                        Period::class,
                                        [$data['period']],
                                        fn(Period $period) =>
                                        "{$period->academic_year}º ano {$period->semester}º semestre de {$period->calendar_year}",
                                ),
                        );

                        ActivityLogService::created(
                                ActivityModules::STUDENTS,
                                "Cadastrou o aluno '{$person->name}'.",
                                $student,
                                array_merge(
                                        [
                                                'registration' => [
                                                        'old' => null,
                                                        'new' => $student->registration,
                                                ],
                                                ActivityLogPrefixes::PERSON . '.name' => [
                                                        'old' => null,
                                                        'new' => $person->name,
                                                ],
                                                ActivityLogPrefixes::USER . '.email' => [
                                                        'old' => null,
                                                        'new' => $user->email,
                                                ],
                                        ],
                                        $changes,
                                ),
                        );

                        $invite = UserInvite::create([
                                'user_id' => $user->id,
                                'university_id' => $universityId,
                                'email' => $user->email,
                                'expires_at' => now()->addDays(1),
                                'token' => Str::uuid(),
                        ]);

                        try {
                                Mail::to($user->email)->send(
                                        new UserInviteMail($invite)
                                );
                        } catch (\Throwable $e) {
                                \Log::error('Erro ao enviar convite de aluno', [
                                        'user_id' => $user->id,
                                        'email' => $user->email,
                                        'error' => $e->getMessage(),
                                ]);
                        }

                        return $student->load([
                                'person',
                                'user',
                                'user.invite',
                                'periods',
                        ]);
                });
        }

        public function deactivate(int $studentId, int $performedByUserId, string $reason, ?string $note = null): Student
        {
                return DB::transaction(function () use ($studentId, $performedByUserId, $reason, $note) {
                        $student = Student::with(['user', 'person'])->findOrFail($studentId);

                        $student->delete();

                        if ($student->user) {
                                $student->user->delete();

                                UserInvite::where('user_id', $student->user->id)
                                        ->whereNull('used_at')
                                        ->update([
                                                'expires_at' => now(),
                                        ]);
                        }

                        if ($student->person) {
                                $student->person->delete();
                        }

                        ActivityLogService::deleted(
                                ActivityModules::STUDENTS,
                                "Inativou o aluno '{$student->person->name}'.",
                                $student,
                                [
                                        'reason' => [
                                                'old' => null,
                                                'new' => $reason,
                                        ],
                                        'note' => [
                                                'old' => null,
                                                'new' => $note,
                                        ],
                                ],
                        );

                        return $student->fresh(['person', 'user.invite', 'periods']);
                });
        }

        public function activate(int $studentId, int $performedByUserId, string $reason, ?string $note = null): Student
        {
                return DB::transaction(function () use ($studentId, $performedByUserId, $reason, $note) {
                        $student = Student::withTrashed()
                                ->with([
                                        'user' => fn($q) => $q->withTrashed(),
                                        'person' => fn($q) => $q->withTrashed(),
                                ])
                                ->findOrFail($studentId);

                        $student->restore();

                        if ($student->user) {
                                $student->user->restore();
                        }

                        if ($student->person) {
                                $student->person->restore();
                        }

                        ActivityLogService::updated(
                                ActivityModules::STUDENTS,
                                "Ativou o aluno '{$student->person->name}'.",
                                $student,
                                [
                                        'reason' => [
                                                'old' => null,
                                                'new' => $reason,
                                        ],
                                        'note' => [
                                                'old' => null,
                                                'new' => $note,
                                        ],
                                ],
                        );

                        return $student->fresh(['person', 'user.invite', 'periods']);
                });
        }

        public function destroy(int $studentId): void
        {
                DB::transaction(function () use ($studentId) {
                        $student = Student::withTrashed()
                                ->with([
                                        'user' => fn($q) => $q->withTrashed(),
                                        'person' => fn($q) => $q->withTrashed(),
                                ])
                                ->findOrFail($studentId);

                        ActivityLogService::deleted(
                                ActivityModules::STUDENTS,
                                "Removeu o aluno '{$student->person->name}'.",
                                $student,
                        );

                        if ($student->user) {
                                UserInvite::where('user_id', $student->user->id)
                                        ->whereNull('used_at')
                                        ->delete();

                                $student->user->forceDelete();
                        }

                        if ($student->person) {
                                $student->person->forceDelete();
                        }

                        $student->periods()->detach();

                        $student->forceDelete();
                });
        }

        public function resendInvite(int $studentId): void
        {
                $student = Student::with(['user', 'user.invite'])->findOrFail($studentId);

                if (!$student->user) {
                        return;
                }

                $invite = UserInvite::where('user_id', $student->user->id)
                        ->whereNull('used_at')
                        ->latest('created_at')
                        ->first();

                if (!$invite) {
                        $invite = UserInvite::create([
                                'user_id' => $student->user->id,
                                'university_id' => $student->university_id,
                                'email' => $student->user->email,
                                'expires_at' => now()->addDays(1),
                                'token' => Str::uuid(),
                        ]);
                }

                Mail::to($student->user->email)->send(
                        new UserInviteMail($invite)
                );
        }

        public function options(int $periodId, int $universityId)
        {
                return Student::query()
                        ->with([
                                'person:id,name',
                        ])
                        ->when($universityId, function ($query) use ($universityId) {
                                $query->where('university_id', $universityId);
                        })
                        ->when($periodId, function ($query) use ($periodId) {
                                $query->whereHas('periods', function ($q) use ($periodId) {
                                        $q->where('periods.id', $periodId)
                                                ->where('student_periods.is_current', true);
                                });
                        })
                        ->orderBy('registration')
                        ->get([
                                'id',
                                'registration',
                                'person_id',
                        ])
                        ->map(function ($student) {
                                return [
                                        'value' => $student->id,
                                        'label' => "{$student->registration} - {$student->person?->name}",
                                ];
                        })
                        ->values();
        }

        public function getOptionsByUniversity(?int $universityId)
        {
                return Student::query()
                        ->when(
                                $universityId,
                                fn($query) => $query->where(
                                        'university_id',
                                        $universityId
                                )
                        )
                        ->with('person:id,name')
                        ->orderBy('id')
                        ->get();
        }

        public function availableClinics(int $studentId)
        {
                $student = Student::query()
                        ->with([
                                'periods' => fn($query) => $query->wherePivot('is_current', true),
                        ])
                        ->findOrFail($studentId);

                $periodIds = $student->periods
                        ->pluck('id');

                return Clinic::query()
                        ->whereHas('scheduleSlots', function ($query) use ($periodIds) {
                                $query->whereIn('period_id', $periodIds);
                        })
                        ->orderBy('name')
                        ->get([
                                'id',
                                'name',
                        ]);
        }

        public function schedule(int $studentId, int $clinicId,): array
        {
                $student = Student::query()
                        ->with('periods')
                        ->findOrFail($studentId);

                $periodIds = $student->periods
                        ->pluck('id');

                $slots = ScheduleSlot::query()
                        ->with([
                                'enrollments' => function ($query) use ($studentId) {
                                        $query->where('student_id', $studentId)
                                                ->where('status', 'active');
                                }
                        ])
                        ->where('clinic_id', $clinicId)
                        ->whereIn('period_id', $periodIds)
                        ->whereHas('enrollments', function ($query) use ($studentId) {
                                $query->where('student_id', $studentId)
                                        ->where('status', 'active');
                        })
                        ->orderBy('date')
                        ->orderBy('start_time')
                        ->get();

                $openDays = $slots
                        ->pluck('date')
                        ->map(fn($date) => $date->format('Y-m-d'))
                        ->unique()
                        ->values();

                return [
                        'open_days' => $openDays,
                        'events' => $slots,
                ];
        }

        public function patients(int $studentId)
        {
                return Student::query()
                        ->findOrFail($studentId)
                        ->patients()
                        ->orderBy('name')
                        ->get();
        }
}
