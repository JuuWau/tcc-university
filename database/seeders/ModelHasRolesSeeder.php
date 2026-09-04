<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModelHasRolesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'juliawauters04@gmail.com')->firstOrFail();
        $receptionist = User::where('email', 'recepcionista@acadent.com.br')->firstOrFail();
        $professor = User::where('email', 'professor@acadent.com.br')->firstOrFail();

        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $receptionistRole = Role::where('slug', 'recepcionist')->firstOrFail();
        $professorRole = Role::where('slug', 'professor')->firstOrFail();
        $studentRole = Role::where('slug', 'student')->firstOrFail();

        $admin->syncRoles([$adminRole]);
        $receptionist->syncRoles([$receptionistRole]);
        $professor->syncRoles([$professorRole]);

        User::whereIn('email', [
            'aluno1@teste.com',
            'aluno2@teste.com',
            'aluno3@teste.com',
            'aluno4@teste.com',
            'aluno5@teste.com',
            'aluno6@teste.com',
            'aluno7@teste.com',
            'aluno8@teste.com',
            'aluno9@teste.com',
            'aluno10@teste.com',
        ])->each(function (User $student) use ($studentRole) {
            $student->syncRoles([$studentRole]);
        });
    }
}
