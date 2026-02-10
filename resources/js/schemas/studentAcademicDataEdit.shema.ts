import { z } from 'zod';

export const studentAcademicDataEditSchema = z
    .object({
        registration: z.string().min(1, 'Registro acadêmico é obrigatório').max(255, 'Registro acadêmico muito longo'),
        period: z.number().min(1, 'Período é obrigatório'),
    });

export type StudentAcademicDataEditForm = z.infer<typeof studentAcademicDataEditSchema>;
