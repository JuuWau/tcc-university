export const STUDENT_ACTIVATION_REASONS = {
    returned_from_leave: {
        label: 'Retorno de licença',
        description: 'O aluno retornou após licença ou afastamento',
        requiresNote: true,
    },
    administrative_correction: {
        label: 'Correção administrativa',
        description: 'O aluno foi ativado devido a correção administrativa',
        requiresNote: false,
    },
    other: {
        label: 'Outro',
        description: '',
        requiresNote: true,
    },
} as const;


export type StudentActivationReasonKey = keyof typeof STUDENT_ACTIVATION_REASONS;
