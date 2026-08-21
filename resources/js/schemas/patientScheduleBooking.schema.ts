import { z } from 'zod';

const normalizeOptionalString = (value: unknown) => {
    if (typeof value !== 'string') return value;
    const trimmed = value.trim();
    return trimmed === '' ? null : trimmed;
};

export const patientScheduleBookingSchema = z
    .object({
        procedure_id: z.number().nullable(),
        start_time: z.preprocess(
            normalizeOptionalString,
            z.string({
                required_error:
                    'Horário de início é obrigatório',
                invalid_type_error:
                    'Horário de início inválido',
            }),
        ),
        end_time: z.preprocess(
            normalizeOptionalString,
            z.string({
                required_error:
                    'Horário de fim é obrigatório',
                invalid_type_error:
                    'Horário de fim inválido',
            }),
        ),
        status: z.enum(
            [
                'scheduled',
                'confirmed',
                'completed',
                'canceled',
                'no_show',
                'rescheduled',
            ],
            {
                required_error:
                    'Status é obrigatório',
                invalid_type_error:
                    'Status inválido',
            },
        ),
        notes: z.preprocess(
            normalizeOptionalString,
            z.string().nullable(),
        ),
    })
    .refine(
        (data) => {
            if (!data.start_time || !data.end_time) {
                return true;
            }

            return data.start_time < data.end_time;
        },
        {
            message:
                'O horário de fim deve ser posterior ao horário de início.',
            path: ['end_time'],
        },
    );

export type PatientScheduleBookingForm = z.infer<typeof patientScheduleBookingSchema>;