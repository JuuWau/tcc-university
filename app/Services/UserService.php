<?php

namespace App\Services;

use App\Data\Users\UserTableFiltersData;
use App\Models\Person;
use App\Models\Role;
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
    public function getAvailableRoles()
    {
        return Role::where('id', '!=', Role::STUDENT)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function paginate(UserTableFiltersData $filters ): LengthAwarePaginator {
        $query = User::withTrashed()
            ->with([
                'person:id,user_id,name',
                'role:id,name,slug',
                'invite' => fn ($q) => $q->select([
                    'user_invites.id',
                    'user_invites.user_id',
                    'user_invites.used_at',
                    'user_invites.expires_at',
                    'user_invites.token',
                ]),
            ])
            ->where('role_id', '!=', Role::STUDENT)
            ->when($filters->universityId, fn($q) => $q->where('users.university_id', $filters->universityId))
            ->whereDoesntHave('student');

        $query->when($filters->search, function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {

                $q->whereHas('person', function ($person) use ($filters) {
                    $person->where('name', 'ilike', "%{$filters->search}%")
                        ->orWhere('phone', 'ilike', "%{$filters->search}%")
                        ->orWhere('cpf', 'ilike', "%{$filters->search}%");
                });

                $q->orWhere('email', 'ilike', "%{$filters->search}%");
            });
        });

        if ($filters->status === 'pending') {
            $query->whereNull('users.deleted_at')
                ->whereNull('users.email_verified_at')
                ->whereHas('invite', fn($q) => $q->whereNull('used_at'));
        } elseif ($filters->status === 'active') {
            $query->whereNull('users.deleted_at')
                ->whereNotNull('users.email_verified_at');
        } elseif ($filters->status === 'inactive') {
            $query->whereNotNull('users.deleted_at');
        }

        if ($filters->sortField === 'name') {
            $query->leftJoin('people', 'users.id', '=', 'people.user_id')
                ->orderBy('people.name', $filters->sortDir)
                ->select('users.*');
        } elseif ($filters->sortField === 'email') {
            $query->orderBy('users.email', $filters->sortDir);
        } else {
            $query->orderBy('users.created_at', $filters->sortDir);
        }

        return $query->paginate($filters->perPage, ['users.*'], 'page', $filters->page);
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
