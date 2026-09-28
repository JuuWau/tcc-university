import { z } from 'zod';

export const specialtySchema = z.object({
        name: z
                .string()
                .min(1, 'O nome da especialidade é obrigatório')
                .min(3, 'O nome deve ter pelo menos 3 caracteres')
                .max(255, 'O nome pode ter no máximo 255 caracteres'),
});

export type SpecialtyForm = z.infer<typeof specialtySchema>;