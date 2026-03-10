<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteStaffRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Role;
use App\Services\UserInviteService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserInviteController extends Controller
{
    protected $userInviteService;

    public function __construct(UserInviteService $userInviteService)
    {
        $this->userInviteService = $userInviteService;
    }

    public function show(string $token, UserInviteService $service)
    {
        try {
            $invite = $service->findValidByToken($token);
        } catch (ModelNotFoundException) {
            return inertia('users/InviteExpired');
        }

        $user = $invite->user;

        return match ($user->role_id) {
            Role::STUDENT => inertia('users/CompleteStudentRegistration', [
                'email' => $user->email,
                'token' => $invite->token,
            ]),
            Role::ADMIN, Role::STAFF => inertia('users/CompleteStaffRegistration', [
                'email' => $user->email,
                'token' => $invite->token,
                'name' => $user->person?->name,
            ]),
            default => abort(403),
        };
    }

    public function store(
        CompleteStaffRequest $request,
        string $token,
        UserInviteService $userInviteService
    ) {
        try {
            $userInviteService->updateStaff($token, $request->validated());
        } catch (ModelNotFoundException) {
            return redirect('/login')
                ->withErrors(['token' => 'Link inválido ou expirado. Solicite um novo convite.']);
        }

        return redirect('/login')
            ->with('success', 'Cadastro concluído com sucesso');
    }

    public function updateStudent(UpdateStudentRequest $request, string $token)
    {
        $this->userInviteService->updateStudent($token, $request->validated());

        return response()->json([
            'message' => 'Estudante atualizado com sucesso',
        ]);
    }
}
