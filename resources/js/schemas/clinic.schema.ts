import { z } from 'zod';

export const clinicSchema = z.object({
    name: z
        .string()
        .trim()
        .min(1, 'Nome da clínica é obrigatório')
        .max(120, 'Nome da clínica não pode ter mais de 120 caracteres'),
    specialty_ids: z
        .array(z.number().int().positive())
        .min(1, 'Selecione pelo menos uma especialidade'),
});

export type ClinicForm = z.infer<typeof clinicSchema>;
