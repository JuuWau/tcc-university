import { z } from 'zod';

export const procedureSchema = z.object({
    name: z
        .string()
        .min(1, 'O nome do procedimento é obrigatório')
        .max(255, 'O nome não pode ter mais de 255 caracteres'),
    specialty_id: z.number({
        required_error: 'A especialidade é obrigatória',
        invalid_type_error: 'A especialidade é obrigatória',
    }),
});

export type ProcedureForm = z.infer<typeof procedureSchema>;
