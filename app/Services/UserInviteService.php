<?php

namespace App\Services;

use App\Models\UserInvite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInviteMail;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserInviteService
{
        public function create(User $user): UserInvite
        {
                UserInvite::where('user_id', $user->id)
                        ->whereNull('used_at')
                        ->update(['used_at' => now()]);

                $invite = UserInvite::create([
                        'user_id' => $user->id,
                        'university_id' => $user->university_id,
                        'email' => $user->email,
                        'token' => Str::uuid(),
                        'expires_at' => now()->addDays(1),
                ]);

                Mail::to($user->email)->send(
                        new UserInviteMail($invite)
                );


                return $invite;
        }

        public function updateStudent(string $token, array $data)
        {
                return DB::transaction(function () use ($token, $data) {
                        $invite = UserInvite::where('token', $token)
                                ->whereNull('used_at')
                                ->firstOrFail();

                        $student = Student::where('user_id', $invite->user_id)
                                ->with(['user', 'person.address'])
                                ->firstOrFail();

                        $student->user->update([
                                'email'    => $data['email'],
                                'password' => Hash::make($data['password']),
                        ]);

                        $student->person->address()->updateOrCreate(
                                [],
                                [
                                        'cep'          => $data['cep'] ?? null,
                                        'street'       => $data['street'] ?? null,
                                        'number'       => $data['number'] ?? null,
                                        'neighborhood' => $data['neighborhood'] ?? null,
                                        'city'         => $data['city'] ?? null,
                                        'state'        => $data['state'] ?? null,
                                        'complement'   => $data['complement'] ?? null,
                                ]
                        );

                        $student->person->update([
                                'user_id' => $student->user_id,
                                'cpf'   => $data['cpf'],
                                'phone' => $data['phone'],
                                'birth_date' => $data['birth_date']
                        ]);

                        $invite->update([
                                'used_at' => now(),
                        ]);

                        return $student->fresh([
                                'user',
                                'person.address',
                        ]);
                });
        }

        public function findValidByToken(string $token): UserInvite
        {
                return UserInvite::where('token', $token)
                        ->whereNull('used_at')
                        ->where('expires_at', '>', now())
                        ->firstOrFail();
        }

        public function updateStaff(string $token, array $data)
        {
                return DB::transaction(function () use ($token, $data) {
                        $invite = UserInvite::where('token', $token)
                                ->whereNull('used_at')
                                ->where('expires_at', '>', now())
                                ->firstOrFail();

                        $user = User::with('person')->findOrFail($invite->user_id);

                        $user->update([
                                'password'    => Hash::make($data['password']),
                                'email_verified_at' => now(),
                        ]);

                        $user->person->address()->updateOrCreate(
                                [],
                                [
                                        'cep'          => $data['cep'] ?? null,
                                        'street'       => $data['street'] ?? null,
                                        'number'       => $data['number'] ?? null,
                                        'neighborhood' => $data['neighborhood'] ?? null,
                                        'city'         => $data['city'] ?? null,
                                        'state'        => $data['state'] ?? null,
                                        'complement'   => $data['complement'] ?? null,
                                ]
                        );

                        $user->person->update([
                                'name'       => $data['name'],
                                'cpf'        => $data['cpf'],
                                'phone'      => $data['phone'],
                                'birth_date' => $data['birth_date'],
                        ]);

                        $invite->update(['used_at' => now()]);

                        return $user->fresh(['person.address']);
                });
        }

        public function completeRegistration(UserInvite $invite, string $password): void
        {
                $invite->user->update([
                        'password' => bcrypt($password),
                        'email_verified_at' => now(),
                ]);

                $invite->update([
                        'used_at' => now(),
                ]);
        }
}
