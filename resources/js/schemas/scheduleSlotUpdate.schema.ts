import { z } from 'zod';

export const scheduleSlotUpdateSchema = z
    .object({
        period_id: z.coerce
            .number({ message: 'Selecione o período.' })
            .int()
            .positive({ message: 'Selecione o período.' }),
        responsible_id: z.coerce
            .number({ message: 'Selecione o responsável.' })
            .int()
            .positive({ message: 'Selecione o responsável.' }),
        date: z.string()
            .min(1, 'Informe a data.')
            .refine((date) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const [year, month, day] = date.split('-').map(Number);
                const selectedDate = new Date(year, month - 1, day);

                return selectedDate >= today;
            }, 'A data não pode ser anterior a hoje.'),
        start_time: z.string()
            .min(1, 'Informe o horário de início.')
            .regex(
                /^([01]\d|2[0-3]):([0-5]\d)$/,
                'O horário deve estar no formato HH:mm.'
            ),
        end_time: z.string()
            .min(1, 'Informe o horário de fim.')
            .regex(
                /^([01]\d|2[0-3]):([0-5]\d)$/,
                'O horário deve estar no formato HH:mm.'
            ),
        available_slots: z.coerce.number().int().min(0, 'Vagas deve ser ≥ 0.'),
        allow_student_booking: z.boolean(),
        allow_student_enrollment: z.boolean(),
        allow_procedure_booking: z.boolean(),
    })
    .refine((data) => data.end_time > data.start_time, {
        message: 'O horário de fim deve ser depois do início.',
        path: ['end_time'],
    });

export type ScheduleSlotUpdateForm = z.infer<typeof scheduleSlotUpdateSchema>;
