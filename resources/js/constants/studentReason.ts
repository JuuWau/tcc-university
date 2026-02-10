export const STUDENT_REASONS = {
    leave_of_absence: {
        label: 'Trancamento',
        description: 'Aluno solicitou pausa temporária nos estudos.',
        reactivatable: true,
        requiresNote: false,
    },
    transfer: {
        label: 'Transferência',
        description: 'Aluno transferido para outra instituição.',
        reactivatable: true,
        requiresNote: false,
    },
    withdrawal: {
        label: 'Desistência',
        description: 'Aluno desistiu do curso.',
        reactivatable: true,
        requiresNote: false,
    },
    graduation: {
        label: 'Conclusão',
        description: 'Aluno concluiu o curso.',
        reactivatable: true,
        requiresNote: false,
    },
    delinquency: {
        label: 'Inadimplência',
        description: 'Aluno desativado por inadimplência financeira.',
        reactivatable: true,
        requiresNote: false,
    },
    administrative: {
        label: 'Administrativo',
        description: 'Inativação por motivo administrativo.',
        reactivatable: true,
        requiresNote: false,
    },
    other: {
        label: 'Outro',
        description: '',
        reactivatable: true,
        requiresNote: true,
    },
} as const;

export type StudentReasonKey = keyof typeof STUDENT_REASONS;
