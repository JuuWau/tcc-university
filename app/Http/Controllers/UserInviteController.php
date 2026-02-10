<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentRequest;
use App\Models\Role;
use App\Services\UserInviteService;
use Illuminate\Http\Request;

class UserInviteController extends Controller
{
    protected $userInviteService;

    public function __construct(UserInviteService $userInviteService)
    {
        $this->userInviteService = $userInviteService;
    }

    public function show(string $token, UserInviteService $service)
    {
        $invite = $service->findValidByToken($token);
        $user = $invite->user;

        return match ($user->role_id) {
            Role::STUDENT => inertia('users/CompleteStudentRegistration', [
                'email' => $user->email,
                'token' => $invite->token,
            ]),
            Role::ADMIN, Role::STAFF => inertia('users/CompleteStaffRegistration', [
                'email' => $user->email,
                'token' => $invite->token,
            ]),
            default => abort(403),
        };
    }

    public function store(
        Request $request,
        string $token,
        UserInviteService $userInviteService
    ) {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $invite = $userInviteService->findValidByToken($token);

        $userInviteService->completeRegistration($invite, $request->password);

        return redirect('/login')
            ->with('success', 'Cadastro concluído com sucesso');
    }

    public function updateStudent(UpdateStudentRequest $request, string $token)
    {
        // dd($token);
        $this->userInviteService->updateStudent($token, $request->validated());

        return response()->json([
            'message' => 'Estudante atualizado com sucesso',
        ]);
    }
}
