import { z } from 'zod';

export const patientStudentEditSchema = z.object({
    student_id: z.number().int().positive().nullable(),
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
