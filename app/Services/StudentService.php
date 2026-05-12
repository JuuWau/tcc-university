<?php

namespace App\Services;

use App\Mail\UserInviteMail;
use App\Models\Address;
use App\Models\User;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\UserActionLog;
use App\Models\UserInvite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        public function paginate(
                int $page = 1,
                int $perPage = 15,
                string $sortField = 'created_at',
                string $sortDir = 'desc',
                string $status = 'all',
                ?int $universityId = null
        ): LengthAwarePaginator {
                $query = Student::withTrashed()
                        ->with([
                                'person:id,name,cpf,phone',
                                'user:id,email',
                                'user.invite:id,user_id,used_at,expires_at,token',
                                'periods:id,academic_year,semester,calendar_year',
                        ])
                        ->when($universityId, fn($q) => $q->where('university_id', $universityId));

                if ($status === 'pending') {
                        $query->whereNull('students.deleted_at')
                                ->whereHas('user.invite', fn($q) => $q->whereNull('used_at'));
                } elseif ($status === 'active') {
                        $query->whereNull('students.deleted_at')
                                ->where(function ($q) {
                                        $q->whereDoesntHave('user')
                                                ->orWhereDoesntHave('user.invite')
                                                ->orWhereHas('user.invite', fn($q) => $q->whereNotNull('used_at'));
                                });
                } elseif ($status === 'inactive') {
                        $query->whereNotNull('students.deleted_at');
                }

                if ($sortField === 'person.name') {
                        $query->join('people', 'students.person_id', '=', 'people.id')
                                ->orderBy('people.name', $sortDir)
                                ->select('students.*');
                } elseif ($sortField === 'registration') {
                        $query->orderBy('students.registration', $sortDir);
                } else {
                        $query->orderBy('students.created_at', $sortDir);
                }

                return $query->paginate($perPage, ['*'], 'page', $page);
        }

        public function find(int $studentId): Student
        {
                return Student::withTrashed()
                        ->with([
                                'person:id,name,cpf,phone,birth_date',
                                'person.address',
                                'user:id,email',
                                'user.invite:id,user_id,used_at,expires_at,token',
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

                        if ($student->user) {
                                $student->user->update([
                                        'email' => $data['email'],
                                ]);
                                if (!empty($data['password'])) {
                                        $student->user->update([
                                                'password' => Hash::make($data['password']),
                                        ]);
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
                                $student->person->update($personData);

                                $addressData = [
                                        'cep' => $data['cep'],
                                        'street' => $data['street'],
                                        'number' => $data['number'],
                                        'neighborhood' => $data['neighborhood'],
                                        'city' => $data['city'],
                                        'state' => $data['state'],
                                        'complement' => $data['complement'] ?? null,
                                ];
                                $student->person->address()->updateOrCreate(
                                        ['addressable_id' => $student->person->id, 'addressable_type' => Person::class],
                                        $addressData
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

                        $student->update([
                                'registration' => $data['registration'],
                        ]);

                        $periodId = (int) $data['period'];

                        DB::table('student_periods')
                                ->where('student_id', $student->id)
                                ->update(['is_current' => false]);

                        if ($student->periods->contains('id', $periodId)) {
                                $student->periods()->updateExistingPivot($periodId, ['is_current' => true]);
                        } else {
                                $student->periods()->attach($periodId, [
                                        'started_at' => now(),
                                        'is_current' => true,
                                ]);
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

        public function create(array $data, int $universityId): Student
        {
                return DB::transaction(function () use ($data, $universityId) {

                        $user = User::create([
                                'email' => $data['email'],
                                'university_id' => $universityId,
                                'role_id' => Role::STUDENT,
                                'password' => Hash::make(Str::random(32)),
                        ]);

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

                        $invite = UserInvite::create([
                                'user_id' => $user->id,
                                'university_id' => $universityId,
                                'email' => $user->email,
                                'expires_at' => now()->addDays(1),
                                'token' => Str::uuid(),
                        ]);

                        Mail::to($user->email)->send(
                                new UserInviteMail($invite)
                        );

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

                        UserActionLog::create([
                                'user_id' => $performedByUserId,
                                'subject_type' => Student::class,
                                'subject_id' => $student->id,
                                'action' => 'student_deactivated',
                                'metadata' => [
                                        'reason' => $reason,
                                        'note' => $note,
                                ],
                        ]);

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

                        UserActionLog::create([
                                'user_id' => $performedByUserId,
                                'subject_type' => Student::class,
                                'subject_id' => $student->id,
                                'action' => 'student_activated',
                                'metadata' => [
                                        'reason' => $reason,
                                        'note' => $note,
                                ],
                        ]);

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
}
