import { z } from 'zod';

export const studentSchema = z.object({
    name: z.string().min(1, 'Nome é obrigatório'),
    registration: z.string().min(1, 'Registro é obrigatório'),
    email: z.string().email('Email inválido'),
    period: z
        .number()
        .nullable()
        .refine((val) => val !== null && val >= 1, { message: 'Selecione um período' }),
});
