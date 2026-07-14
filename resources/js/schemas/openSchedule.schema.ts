import { z } from 'zod';

export const openScheduleSchema = z
    .object({
        clinic_id: z.number({
            required_error: 'Selecione a clínica',
            invalid_type_error: 'Selecione a clínica',
        }),
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
        period_id: z.number({
            required_error: 'Selecione um período',
            invalid_type_error: 'Selecione um período',
        }),
        responsible_ids: z.array(z.number()).min(1, 'Selecione ao menos um responsável'),
        days: z.array(z.string()).min(1, 'Selecione pelo menos um dia'),
        start_time: z
            .string()
            .min(1, 'Informe o horário de início')
            .regex(/^\d{2}:\d{2}$/, 'Horário de início inválido'),
        end_time: z
            .string()
            .min(1, 'Informe o horário de fim')
            .regex(/^\d{2}:\d{2}$/, 'Horário de fim inválido'),
        allow_student_booking: z.boolean(),
        allow_student_enrollment: z.boolean(),
        allow_procedure_booking: z.boolean(),
    })
    .refine((data) => data.end_time > data.start_time, {
        message: 'Horário de fim deve ser maior que o horário de início',
        path: ['end_time'],
    });

export type OpenScheduleForm = z.infer<typeof openScheduleSchema>;
