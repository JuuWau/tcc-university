<?php

namespace App\Services;

use App\Constants\ActivityLogPrefixes;
use App\Constants\ActivityModules;
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

    public function paginate(UserTableFiltersData $filters): LengthAwarePaginator
    {
        $query = User::withTrashed()
            ->with([
                'person:id,user_id,name',
                'roles:id,name,slug',
                'invite' => fn($q) => $q->select([
                    'user_invites.id',
                    'user_invites.user_id',
                    'user_invites.used_at',
                    'user_invites.expires_at',
                    'user_invites.token',
                ]),
            ])
            ->whereHas('roles', function ($query) {
                $query->where('slug', '!=', 'student');
            })
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
                'university_id' => $universityId,
                'password' => Hash::make(Str::random(32)),
            ]);

            $role = Role::findOrFail($data['role_id']);

            $user->assignRole($role);

            $person = Person::create([
                'user_id' => $user->id,
                'university_id' => $universityId,
                'name' => $data['name'],
            ]);

            $this->userInviteService->create($user);

            $changes = ActivityLogService::getCreatedChanges(
                $user,
                ActivityLogPrefixes::USER,
            );

            $changes = array_merge(
                $changes,
                ActivityLogService::getCreatedChanges(
                    $person,
                    ActivityLogPrefixes::PERSON,
                ),
            );

            ActivityLogService::trackBelongsToChange(
                $changes,
                'user.role_id',
                ActivityLogPrefixes::ROLE,
                Role::class,
                null,
                $user->role_id,
            );

            ActivityLogService::created(
                ActivityModules::USERS,
                "Cadastrou o usuário '{$person->name}'.",
                $user,
                $changes,
            );

            return $user->load([
                'person',
                'roles',
                'invite',
            ]);
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
                'person' => fn($q) => $q->withTrashed()->with('address'),
                'roles',
                'invite',
            ])
            ->whereDoesntHave('student')
            ->findOrFail($userId);
    }

    public function deactivate(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $user = User::where('id', $userId)
                ->whereDoesntHave('student')
                ->whereNull('deleted_at')
                ->with('person')
                ->firstOrFail();

            $user->fill([
                'deleted_at' => now(),
            ]);

            $changes = ActivityLogService::getChanges($user);

            $user->delete();

            $name = $user->person?->name ?? $user->email;

            ActivityLogService::deleted(
                ActivityModules::USERS,
                "Inativou o usuário '{$name}'.",
                $user,
                $changes,
            );
        });
    }

    public function activate(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $user = User::withTrashed()
                ->with(['person' => fn($q) => $q->withTrashed()])
                ->whereDoesntHave('student')
                ->findOrFail($userId);

            $user->fill([
                'deleted_at' => null,
            ]);

            $changes = ActivityLogService::getChanges($user);

            $user->restore();

            if ($user->relationLoaded('person') && $user->person) {
                $user->person->restore();
            }

            $name = $user->person?->name ?? $user->email;

            ActivityLogService::updated(
                ActivityModules::USERS,
                "Ativou o usuário '{$name}'.",
                $user,
                $changes,
            );
        });
    }

    public function destroy(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $user = User::withTrashed()
                ->with(['person' => fn($q) => $q->withTrashed()])
                ->whereDoesntHave('student')
                ->findOrFail($userId);

            $changes = ActivityLogService::getCreatedChanges($user);

            UserInvite::where('user_id', $user->id)->delete();

            if ($user->person) {
                $user->person->forceDelete();
            }

            $name = $user->person?->name ?? $user->email;

            ActivityLogService::deleted(
                ActivityModules::USERS,
                "Removeu o usuário '{$name}'.",
                $user,
                $changes,
            );

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

            $changes = [];

            $user->fill([
                'email' => $data['email'],
            ]);

            $changes = array_merge(
                $changes,
                ActivityLogService::getChanges(
                    $user,
                    ActivityLogPrefixes::USER,
                ),
            );

            $user->save();

            if (!empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);

                $changes['user.password'] = [
                    'old' => '********',
                    'new' => '********',
                ];
            }

            if ($user->person) {
                $personData = [
                    'name' => $data['name'] ?? $user->person->name,
                    'cpf' => $data['cpf'],
                    'phone' => $data['phone'],
                    'birth_date' => $data['birth_date'],
                ];

                $user->person->fill($personData);

                $changes = array_merge(
                    $changes,
                    ActivityLogService::getChanges(
                        $user->person,
                        ActivityLogPrefixes::PERSON,
                    ),
                );

                $user->person->save();

                $address = $user->person->address()->firstOrNew([
                    'addressable_id' => $user->person->id,
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
                    ActivityLogService::getChanges(
                        $address,
                        ActivityLogPrefixes::ADDRESS,
                    ),
                );

                $address->save();
            }

            if (!empty($changes)) {
                $name = $user->person?->name ?? $user->email;

                ActivityLogService::updated(
                    ActivityModules::USERS,
                    "Atualizou os dados pessoais do usuário '{$name}'.",
                    $user,
                    $changes,
                );
            }

            return $user->fresh([
                'person',
                'person.address',
                'roles',
                'invite',
            ]);
        });
    }

    public function updateRole(int $userId, int $roleId): User
    {
        return DB::transaction(function () use ($userId, $roleId) {
            $user = User::withTrashed()
                ->with(['person'])
                ->whereDoesntHave('student')
                ->findOrFail($userId);

            $role = Role::findOrFail($roleId);

            $user->syncRoles([$role]);

            $changes = ActivityLogService::getChanges($user);

            ActivityLogService::trackBelongsToChange(
                $changes,
                'role_id',
                ActivityLogPrefixes::ROLE,
                Role::class,
                $user->getOriginal('role_id'),
                $roleId,
            );

            if (empty($changes)) {
                return $user;
            }

            $user->save();

            $name = $user->person?->name ?? $user->email;

            ActivityLogService::updated(
                ActivityModules::USERS,
                "Atualizou o perfil de acesso do usuário '{$name}'.",
                $user,
                $changes,
            );

            return $user->fresh([
                'person',
                'roles',
                'invite',
            ]);
        });
    }

    public function getResponsible(?int $universityId)
    {
        return User::query()
            ->when(
                $universityId,
                fn($q) => $q->where('university_id', $universityId)
            )
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('id', [
                    Role::STUDENT,
                    Role::RECEPTIONIST,
                ]);
            })
            ->with('person:id,user_id,name')
            ->with('roles:id,name')
            ->get(['id'])
            ->map(fn($user) => [
                'id' => $user->id,
                'label' => $user->person?->name ?? '—',
            ])
            ->sortBy('label')
            ->values();
    }
}
