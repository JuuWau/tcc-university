import { z } from 'zod';

export const periodSchema = z.object({
  academic_year: z
    .string()
    .length(1, 'O ano acadêmico é obrigatório')
    .regex(/^\d$/, 'O ano acadêmico deve ser numérico'),

  semester: z
    .string()
    .length(1, 'O semestre é obrigatório')
    .regex(/^\d$/, 'O semestre deve ser numérico'),

  calendar_year: z
    .string()
    .length(4, 'O ano calendário é obrigatório')
    .regex(/^\d{4}$/, 'O ano calendário deve ser numérico'),

  specialties: z
    .array(z.number())
    .min(1, 'Selecione pelo menos uma especialidade'),
});


export type PeriodForm = z.infer<typeof periodSchema>;