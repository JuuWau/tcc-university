import { z } from 'zod';

export const scheduleSlotCreateSchema = z
    .object({
        period_id: z.coerce.number({ message: 'Selecione o período.' })
            .int()
            .positive({ message: 'Selecione o período.' }),
        responsible_id: z.coerce
            .number({ message: 'Selecione o responsável.' })
            .int()
            .positive({ message: 'Selecione o responsável.' })
            .nullable(),
        date: z.string().min(1, 'Informe a data.'),
        start_time: z.string().min(1, 'Informe o horário de início.'),
        end_time: z.string().min(1, 'Informe o horário de fim.'),
        allow_student_enrollment: z.boolean(),
        allow_student_booking: z.boolean(),
    })
    .refine((data) => data.end_time > data.start_time, {
        message: 'O horário de fim deve ser depois do início.',
        path: ['end_time'],
    });

export type ScheduleSlotCreateForm = z.infer<typeof scheduleSlotCreateSchema>;
