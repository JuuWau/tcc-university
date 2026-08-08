import { z } from 'zod';

const dateSchema = z.string().regex(
    /^\d{4}-\d{2}-\d{2}$/,
    'Data inválida.',
);

export const appointmentsReportFiltersSchema = z
    .object({
        clinic_id: z.number().nullable(),
        responsible_id: z.number().nullable(),
        student_id: z.number().nullable(),
        period_id: z.number().nullable(),
        search: z.string(),
        status: z.string().nullable(),
        start_date: dateSchema,
        end_date: dateSchema,
    })
    .refine(
        (data) => {
            const start = new Date(`${data.start_date}T00:00:00`);
            const end = new Date(`${data.end_date}T00:00:00`);

            return start <= end;
        },
        {
            message: 'A data inicial deve ser anterior à data final.',
            path: ['end_date'],
        },
    )
    .refine(
        (data) => {
            const start = new Date(`${data.start_date}T00:00:00`);
            const end = new Date(`${data.end_date}T00:00:00`);

            const maxDate = new Date(start);
            maxDate.setFullYear(maxDate.getFullYear() + 1);

            return end <= maxDate;
        },
        {
            message: 'O período máximo para consulta é de 12 meses.',
            path: ['end_date'],
        },
    );

export type AppointmentsFiltersSchema = z.infer<typeof appointmentsReportFiltersSchema>;