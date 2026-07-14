import { z } from 'zod';

export const appointmentCreateSchema = z.object({
    patient_id: z
        .number({
            required_error: 'Selecione um paciente',
        })
        .nullable()
        .refine(
            value => value !== null,
            'Selecione um paciente',
        ),
    procedure_id: z
        .number()
        .nullable()
        .optional(),
    status: z
        .string()
        .min(
            1,
            'Selecione um status',
        ),
    date: z
        .string()
        .min(
            1,
            'Informe a data',
        )
        .refine(
            (value) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const [year, month, day] = value.split('-').map(Number);
                const selectedDate = new Date(year, month - 1, day);

                return selectedDate >= today;
            },
            'Não é possível agendar em dias que já passaram',
        ),
    start_time: z
        .string()
        .regex(
            /^([01]\d|2[0-3]):([0-5]\d)$/,
            'Horário inicial inválido',
        ),
    end_time: z
        .string()
        .regex(
            /^([01]\d|2[0-3]):([0-5]\d)$/,
            'Horário final inválido',
        ),
    notes: z
        .string()
        .max(
            1000,
            'As observações devem ter no máximo 1000 caracteres',
        )
        .optional(),
}).refine(
    data => data.start_time < data.end_time,
    {
        message: 'O horário final deve ser maior que o horário inicial',
        path: ['end_time'],
    },
);