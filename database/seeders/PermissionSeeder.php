<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'specialties.view', // Permissão para acessar página de cadastros de especialidades
            'specialties.create', // Permissão para criar especialidade
            'specialties.update', // Permissão para editar especialidade
            'specialties.delete', // Permissão para deletar especialidade

            'procedures.view', // Permissão para acessar página de cadastros de procedimentos
            'procedures.create', // Permissão para criar procedimento
            'procedures.update', // Permissão para editar procedimento
            'procedures.delete', // Permissão para deletar procedimento

            'periods.view', // Permissão para acessar página de cadastros de períodos
            'periods.create', // Permissão para criar período
            'periods.update', // Permissão para editar período
            'periods.delete', // Permissão para deletar período

            'clinics.view', // Permissão para acessar página de cadastros de clínicas
            'clinics.create', // Permissão para criar clínica
            'clinics.update', // Permissão para editar clínica
            'clinics.deactivate', // Permissão para desativar clínica
            'clinics.delete', // Permissão para deletar clínica

            'action-logs.view', // Permissão para acessar página de ação de logs

            'open-schedule.open', // Permissão para abrir dias na agenda de clínicas específicas
            'open-schedule-management.view', // Permissão para gerenciar agenda de clínicas específicas
            'open-schedule-management.createSlot', // Permissão para criar slot na agenda de clínicas específicas
            'open-schedule-management.deleteSlot', // Permissão para deletar slot na agenda de clínicas específicas
            'open-schedule-management.updateSlot', // Permissão para editar agenda de clínicas específicas
            'open-schedule-management.enrollStudent', // Permissão para adicionar estudante à no slot da agenda de clínicas específicas
            'open-schedule-management-student.view', // Permissão para acessar página de inscrição em clínicas específicas (estudante)
            'open-schedule-management-student.enroll', // Permissão para se inscrever em clínicas específicas (estudante)
        
            'students.view', // Permissão para acessar página de cadastros de estudantes
            'students.create', // Permissão para criar estudante
            'students.delete', // Permissão para deletar estudante
            'students.deactivate', // Permissão para desativar estudante
            'students.activate', // Permissão para ativar estudante
            'students.invite', // Permissão para reenviar convite de estudante
            'students.personal-page.view', // Permissão para acessar pagina do estudante
            'students.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do estudante
            'students.personal-page.updateHeaderData', // Permissão para atualizar dados do header do estudante
            'students.personal-page.viewSchedule', // Permissão para acessar tab de agendamentos do estudante
            'students.personal-page.updateSchedule', // Permissão para gerenciar tab de agendamentos do estudante

            'patients.view', //Permissão para acessar página
            'patients.create', // Permissão para criar paciente
            'patients.update', // Permissão para atualizar status paciente
            'patients.import', // Permissão para importar paciente
            'patients.deactivate', // Permissão para desativar paciente
            'patients.delete', // Permissão para excluir paciente
            'patients.personal-page.view', // Permissão para acessar pagina do paciente
            'patients.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do paciente
            'patients.personal-page.updateHeaderData', // Permissão para atualizar dados do header do paciente
            'patients.personal-page.viewAppointments', // Permissão para acessar tab de agendamentos do paciente
            'patients.personal-page.manageAppointments', // Permissão para gerenciar tab de agendamentos do paciente
            'patients.personal-page.viewClinics', // Permissão para acessar tab de clínicas do paciente
            'patients.personal-page.addPatientToWaitingList', // Permissão para adicionar paciente a lista de espera
            'patients.personal-page.removeEnrollmentClinic', // Permissão remover inscrição em clínicas do paciente
            'patients.personal-page.enrollClinic', // Permissão para adicionar paciente a clínicas

            'users.view', // Permissão para acessar página de cadastros de usuários
            'users.create', // Permissão para criar usuário
            'users.update', // Permissão para editar usuário
            'users.deactivate', // Permissão para desativar usuário
            'users.invite', // Permissão para convidar usuário
            'users.delete', // Permissão para excluir usuário
            'users.personal-page.view', // Permissão para acessar pagina do usuário
            'users.personal-page.updateRole', // Permissão para gerenciar papéis do usuário
            'users.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do usuário

            'appointments-confirmation.view', // Permissão para acessar página de confirmação de agendamento
            'appointments-confirmation.update', // Permissão para alterar os status dos pacientes na pagina de confirmação
            'appointments-confirmation-student.view', // Permissão para mostrar o agendamento dos próprios pacientes na pagina de confirmação

            'clinics-management.view', // Gerênciar clinicas acesso total (página)
            'clinics-management.addPatientToWaitingList', // Adicionar paciente a lista de espera
            'clinics-management.removeEnrollmentClinic', // Remover paciente da clínica
            'clinics-management.enrollClinic', // Adicionar paciente a clínica

            'attendance.view',  // Permissao para acessar página de comparecimento dos estudantes
            'attendance.update',  // Atualizar compareciemento dos estudantes

            'students-reports.view', // Página de relatórios de estudantes
            'appointments-reports.view', // Página de relatórios de agendamentos
            'patients-reports.view', // Página de relatórios de pacientes
            'clinics-reports.view' // Página de relatórios de clínicas
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::where('slug', 'admin')->firstOrFail();

        $permissions = Permission::where(
            'name',
            '!=',
            'open-schedule-management-student.view'
        )->get();

        $admin->syncPermissions($permissions);

        $student = Role::where('slug', 'student')->firstOrFail();

        $student->syncPermissions([
            'patients.view', // Permissão para acessar página de cadastros de pacientes
            'students.personal-page.view', // Permissão para acessar pagina do estudante
            'students.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do estudante
            'appointments-confirmation.view', // permissão para acessar a página de confirmação de agendamento
            'appointments-confirmation-student.view', // permissão para mostrar o agendamento dos próprios pacientes na pagina de confirmação
            'appointments-confirmation.update', // permissão para alterar os status dos próprios pacientes na pagina de confirmação

            'open-schedule-management-student.view', //permissão para se inscrever nas clinicas
            'open-schedule-management-student.enroll', // Permissão para se inscrever em clínicas específicas (estudante)

            'patients.personal-page.view', // Permissão para acessar pagina do paciente
            'patients.personal-page.viewAppointments', // Permissão para acessar tab de agendamentos do paciente
            'patients.personal-page.viewClinics', // Permissão para acessar tab de clínicas do paciente

            'patients.personal-page.view',
        ]);

        $receptionist = Role::where('slug', 'recepcionist')->firstOrFail();

        $receptionist->syncPermissions([
            'specialties.view', // Permissão para acessar página de cadastros de especialidades
            'specialties.create', // Permissão para criar especialidade
            'specialties.update', // Permissão para editar especialidade
            'specialties.delete', // Permissão para deletar especialidade

            'procedures.view', // Permissão para acessar página de cadastros de procedimentos
            'procedures.create', // Permissão para criar procedimento
            'procedures.update', // Permissão para editar procedimento
            'procedures.delete', // Permissão para deletar procedimento

            'periods.view', // Permissão para acessar página de cadastros de períodos
            'periods.create', // Permissão para criar período
            'periods.update', // Permissão para editar período
            'periods.delete', // Permissão para deletar período

            'clinics.view', // Permissão para acessar página de cadastros de clínicas
            'clinics.create', // Permissão para criar clínica
            'clinics.update', // Permissão para editar clínica
        
            'students.view', // Permissão para acessar página de cadastros de estudantes
            'students.personal-page.view', // Permissão para acessar pagina do estudante
            'students.personal-page.viewSchedule', // Permissão para acessar tab de agendamentos do estudante
            'students.personal-page.updateSchedule', // Permissão para gerenciar tab de agendamentos do estudante

            'users.personal-page.view', // Permissão para acessar pagina do usuário
            'users.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do usuário

            'patients.view', // Permissão para acessar página de cadastros de pacientes
            'patients.create', // Permissão para criar paciente
            'patients.update', // Permissão para atualizar status paciente
            'patients.import', // Permissão para importar paciente
            'patients.deactivate', // Permissão para desativar paciente
            'patients.personal-page.view', // Permissão para acessar pagina do paciente
            'patients.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do paciente
            'patients.personal-page.updateHeaderData', // Permissão para atualizar dados do header do paciente
            'patients.personal-page.viewAppointments', // Permissão para acessar tab de agendamentos do paciente
            'patients.personal-page.viewAppointments', // Permissão para acessar tab de agendamentos do paciente
            'patients.personal-page.manageAppointments', // Permissão para gerenciar tab de agendamentos do paciente
            'patients.personal-page.viewClinics', // Permissão para acessar tab de clínicas do paciente
            'patients.personal-page.addPatientToWaitingList', // Permissão para adicionar paciente a lista de espera
            'patients.personal-page.removeEnrollmentClinic', // Permissão remover inscrição em clínicas do paciente
            'patients.personal-page.enrollClinic', // Permissão para adicionar paciente a clínicas

            'appointments-confirmation.view', // permissão para confirmação pacientes
            'appointments-confirmation.update', // permissão para alterar os status dos pacientes na pagina de confirmação

            'clinics-management.view', // Gerênciar clinicas acesso total (página)
            'clinics-management.addPatientToWaitingList', // Adicionar paciente a lista de espera
            'clinics-management.removeEnrollmentClinic', // Remover paciente da clínica
            'clinics-management.enrollClinic', // Adicionar paciente a clínica
        ]);

        $professor = Role::where('slug', 'professor')->firstOrFail();

        $professor->syncPermissions([
            'students.personal-page.view', // Permissão para acessar pagina do estudante
            'students.personal-page.viewSchedule', // Permissão para acessar tab de agendamentos do estudante

            'patients.personal-page.view', // Permissão para acessar pagina do paciente
            'patients.personal-page.viewAppointments', // Permissão para acessar tab de agendamentos do paciente
            'patients.personal-page.viewClinics', // Permissão para acessar tab de clínicas do paciente

            'users.personal-page.view', // Permissão para acessar pagina do usuário
            'users.personal-page.updatePersonalData', // Permissão para atualizar dados pessoais do usuário

            'attendance.view',  // Permissao para acessar página de comparecimento dos estudantes
            'attendance.update',  // Gerênciar compareciemento estudante
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
