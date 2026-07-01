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
        date: z.string()
            .min(1, 'Informe a data.')
            .refine((date) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const [year, month, day] = date.split('-').map(Number);
                const selectedDate = new Date(year, month - 1, day);

                return selectedDate >= today;
            }, 'A data não pode ser anterior a hoje.'),
        available_slots: z.preprocess(
            (value) => {
                if (value === '' || value === undefined) {
                    return null;
                }

                return Number(value);
            },
            z
                .number({
                    invalid_type_error: 'Cadeiras livres deve ser um número',
                })
                .int('Cadeiras livres deve ser um número inteiro')
                .min(0, 'Cadeiras livres não pode ser negativo')
                .nullable(),
        ),
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
        allow_student_enrollment: z.boolean(),
        allow_student_booking: z.boolean(),
        allow_procedure_booking: z.boolean(),
    })
    .refine((data) => data.end_time > data.start_time, {
        message: 'O horário de fim deve ser depois do início.',
        path: ['end_time'],
    });

export type ScheduleSlotCreateForm = z.infer<typeof scheduleSlotCreateSchema>;
