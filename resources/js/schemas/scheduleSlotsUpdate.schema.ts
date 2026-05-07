import { z } from 'zod';

export const scheduleSlotsUpdateSchema = z
    .object({
        ids: z.array(z.number()).min(1, 'Selecione pelo menos um horário.'),
        responsible_id: z.coerce
        .number({ message: 'Selecione o responsável.' })
        .int()
        .positive({ message: 'Selecione o responsável.' }),

        start_time: z.string().min(1, 'Informe o horário de início.'),
        end_time: z.string().min(1, 'Informe o horário de fim.'),
        available_slots: z.coerce
            .number({ message: 'Informe as vagas.' })
            .int()
            .min(0, { message: 'As vagas não podem ser negativas.' })
            .optional(),
    })
    .refine((data) => data.end_time > data.start_time, {
        message: 'O horário de fim deve ser depois do início.',
        path: ['end_time'],
    });

export type ScheduleSlotsUpdateForm = z.infer<typeof scheduleSlotsUpdateSchema>;
