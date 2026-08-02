<?php

namespace App\Constants;

class ActivityModules
{
    public const SPECIALTIES = 'Especialidades';
    public const PERIODS = 'Períodos';
    public const CLINICS = 'Clínicas';
    public const PATIENTS = 'Pacientes';
    public const USERS = 'Usuários';
    public const PROCEDURES = 'Procedimentos';
    public const STUDENTS = 'Estudantes';
    public const SCHEDULES = 'Agendas';
    public const ENROLLMENTS = 'Matrículas';
    public const APPOINTMENTS = 'Agendamentos';

    public static function all(): array
    {
        return [
            self::SPECIALTIES,
            self::PERIODS,
            self::CLINICS,
            self::PATIENTS,
            self::USERS,
            self::PROCEDURES,
            self::STUDENTS,
            self::SCHEDULES,
            self::ENROLLMENTS,
            self::APPOINTMENTS,
        ];
    }
}
