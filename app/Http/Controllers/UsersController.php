<?php

namespace App\Http\Controllers;

use App\Data\Users\UserTableFiltersData;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\TableUserRequest;
use App\Http\Requests\UpdateUserPersonalDataRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserTableResource;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        return Inertia::render('users/UsersIndex', [
            'roles' => $this->userService->getAvailableRoles(),
        ]);
    }

    public function table(TableUserRequest $request)
    {
        $users = $this->userService->paginate(
            UserTableFiltersData::fromRequest($request)
        );

        return UserTableResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create(
            $request->validated(),
            $request->user()->university_id
        );

        return response()->json([
            'message' => 'Convite enviado com sucesso',
            'user' => $user,
        ]);
    }

    public function resendInvite(int $user)
    {
        $this->userService->resendInvite($user);

        return response()->json([
            'message' => 'Convite reenviado com sucesso',
        ]);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $model = $this->userService->find($user->id);

        $roles = Role::where('id', '!=', Role::STUDENT)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('users/UserTab', [
            'user' => $model,
            'roles' => $roles,
        ]);
    }

    public function deactivate(int $user)
    {
        $this->userService->deactivate($user);

        return response()->json([
            'message' => 'Colaborador inativado com sucesso',
        ]);
    }

    public function activate(int $user)
    {
        $this->userService->activate($user);

        return response()->json([
            'message' => 'Colaborador ativado com sucesso',
        ]);
    }

    public function destroy(int $user)
    {
        $this->userService->destroy($user);

        return response()->json([
            'message' => 'Usuário excluído com sucesso',
        ]);
    }

    public function update(UpdateUserPersonalDataRequest $request, int $user)
    {
        $updated = $this->userService->updatePersonalData($user, $request->validated());

        return response()->json([
            'message' => 'Dados pessoais atualizados com sucesso',
            'user' => $updated,
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, int $user)
    {
        $updated = $this->userService->updateRole($user, $request->validated('role_id'));

        return response()->json([
            'message' => 'Perfil atualizado com sucesso',
            'user' => $updated,
        ]);
    }
}
