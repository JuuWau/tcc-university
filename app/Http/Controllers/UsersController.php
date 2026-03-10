<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPersonalDataRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\Role;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        $roles = Role::where('id', '!=', Role::STUDENT)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('users/UsersIndex', [
            'roles' => $roles,
        ]);
    }

    public function table(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'sort_field' => ['sometimes', 'string', 'in:name,email,created_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
            'status' => ['sometimes', 'string', 'in:all,pending,active,inactive'],
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 15;
        $sortField = $validated['sort_field'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $status = $validated['status'] ?? 'all';

        $paginator = $this->userService->paginate(
            $page,
            $perPage,
            $sortField,
            $sortDir,
            $status,
            $request->user()?->university_id
        );

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
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

    public function show(int $user)
    {
        $model = $this->userService->find($user);
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
