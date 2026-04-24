<?php

namespace App\Services;

use App\Models\Person;
use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        protected UserInviteService $userInviteService
    ) {}

    /**
     * @param  'all'|'pending'|'active'|'inactive'  $status
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
        $query = User::withTrashed()
            ->with([
                'person:id,user_id,name',
                'role:id,name,slug',
                'invite:id,user_id,used_at,expires_at,token',
            ])
            ->when($universityId, fn($q) => $q->where('users.university_id', $universityId))
            ->whereDoesntHave('student');

        if ($status === 'pending') {
            $query->whereNull('users.deleted_at')
                ->whereNull('users.email_verified_at')
                ->whereHas('invite', fn($q) => $q->whereNull('used_at'));
        } elseif ($status === 'active') {
            $query->whereNull('users.deleted_at')
                ->whereNotNull('users.email_verified_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('users.deleted_at');
        }

        if ($sortField === 'name') {
            $query->leftJoin('people', 'users.id', '=', 'people.user_id')
                ->orderBy('people.name', $sortDir)
                ->select('users.*');
        } elseif ($sortField === 'email') {
            $query->orderBy('users.email', $sortDir);
        } else {
            $query->orderBy('users.created_at', $sortDir);
        }

        return $query->paginate($perPage, ['users.*'], 'page', $page);
    }

    public function create(array $data, int $universityId): User
    {
        return DB::transaction(function () use ($data, $universityId) {
            $user = User::create([
                'email' => $data['email'],
                'role_id' => $data['role_id'],
                'university_id' => $universityId,
                'password' => Hash::make(Str::random(32)),
            ]);

            Person::create([
                'user_id' => $user->id,
                'university_id' => $universityId,
                'name' => $data['name'],
            ]);

            $this->userInviteService->create($user);

            return $user->load(['person', 'role', 'invite']);
        });
    }

    public function resendInvite(int $userId): void
    {
        $user = User::where('id', $userId)
            ->whereDoesntHave('student')
            ->firstOrFail();

        $this->userInviteService->create($user);
    }

    public function find(int $userId): User
    {
        return User::withTrashed()
            ->with([
                'person' => fn ($q) => $q->withTrashed()->with('address'),
                'role',
                'invite',
            ])
            ->whereDoesntHave('student')
            ->findOrFail($userId);
    }

    public function deactivate(int $userId): void
    {
        $user = User::where('id', $userId)
            ->whereDoesntHave('student')
            ->whereNull('deleted_at')
            ->firstOrFail();

        $user->delete();
    }

    public function activate(int $userId): void
    {
        $user = User::withTrashed()
            ->with(['person' => fn ($q) => $q->withTrashed()])
            ->whereDoesntHave('student')
            ->findOrFail($userId);

        $user->restore();

        if ($user->relationLoaded('person') && $user->person) {
            $user->person->restore();
        }
    }

    public function destroy(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $user = User::withTrashed()
                ->with(['person' => fn ($q) => $q->withTrashed()])
                ->whereDoesntHave('student')
                ->findOrFail($userId);

            UserInvite::where('user_id', $user->id)->delete();

            if ($user->person) {
                $user->person->forceDelete();
            }

            $user->forceDelete();
        });
    }

    public function updatePersonalData(int $userId, array $data): User
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::withTrashed()
                ->with(['person', 'person.address'])
                ->whereDoesntHave('student')
                ->findOrFail($userId);

            $user->update([
                'email' => $data['email'],
            ]);
            if (! empty($data['password'] ?? '')) {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            if ($user->person) {
                $personData = [
                    'name' => $data['name'] ?? $user->person->name,
                    'cpf' => $data['cpf'],
                    'phone' => $data['phone'],
                    'birth_date' => $data['birth_date'],
                ];
                $user->person->update($personData);

                $addressData = [
                    'cep' => $data['cep'],
                    'street' => $data['street'],
                    'number' => $data['number'],
                    'neighborhood' => $data['neighborhood'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'complement' => $data['complement'] ?? null,
                ];
                $user->person->address()->updateOrCreate(
                    ['addressable_id' => $user->person->id, 'addressable_type' => Person::class],
                    $addressData
                );
            }

            return $user->fresh(['person', 'person.address', 'role', 'invite']);
        });
    }

    public function updateRole(int $userId, int $roleId): User
    {
        $user = User::withTrashed()
            ->whereDoesntHave('student')
            ->findOrFail($userId);

        $user->update(['role_id' => $roleId]);

        return $user->fresh(['person', 'role', 'invite']);
    }

    public function getResponsible(?int $universityId)
    {
        return User::query()
            ->when($universityId, fn($q) => $q->where('university_id', $universityId))
            ->whereHas('role', fn ($q) => 
                $q->where('slug', '!=', 'student')
            )
            ->with('person:id,user_id,name')
            ->with('role:id,user_id,name')
            ->get(['id'])
            ->map(fn($user) => [
                'id' => $user->id,
                'label' => $user->person?->name ?? '—',
            ])
            ->sortBy('label')
            ->values();
    }
}
