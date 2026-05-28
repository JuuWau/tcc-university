import { z } from 'zod';

export const patientStudentEditSchema = z.object({
    code: z
        .string()
        .trim()
        .min(1, 'Código do paciente é obrigatório')
        .max(20, 'Código não pode ter mais de 20 caracteres'),
    student_ids: z.number().array().min(0).nullable(),
    status: z.enum([
        'ativo',
        'inativo',
        'tratamento',
        'pausa_tratamento',
        'abandono',
        'concluido',
        'transferencia',
    ]),
});

export type PatientStudentEditForm = z.infer<typeof patientStudentEditSchema>;
