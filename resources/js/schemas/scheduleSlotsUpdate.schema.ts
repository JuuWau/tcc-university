import { z } from 'zod';
import { formatDateBr } from '@/src/utils/formatters';

export const scheduleSlotsUpdateSchema = z
    .object({
        ids: z.array(z.number()).min(1, 'Selecione pelo menos um horário.'),
        responsible_ids: z.array(
            z.coerce.number().int()
            .positive({ message: 'Selecione o responsável.' })
            .nullable()),
        slots_data: z.array(z.object({
            id: z.number(),
            date: z.string(),
        })).optional(),
        start_time: z.string().min(1, 'Informe o horário de início.'),
        end_time: z.string().min(1, 'Informe o horário de fim.'),
        available_slots: z.coerce
            .number({ message: 'Informe as vagas.' })
            .int()
            .min(0, { message: 'As vagas não podem ser negativas.' })
            .optional(),
        allow_student_enrollment: z.boolean(),
        allow_student_booking: z.boolean(),
        allow_procedure_booking: z.boolean(),
    })
    .refine((data) => data.end_time > data.start_time, {
        message: 'O horário de fim deve ser depois do início.',
        path: ['end_time'],
    })
    .superRefine((data, ctx) => {
        if (!data.slots_data || data.slots_data.length === 0) {
            return;
        }

        const dateCount = data.slots_data.reduce((acc, slot) => {
            acc[slot.date] = (acc[slot.date] || 0) + 1;
            return acc;
        }, {} as Record<string, number>);

        const duplicateDates = Object.entries(dateCount)
            .filter(([_, count]) => count > 1)
            .map(([date]) => date);

        if (duplicateDates.length > 0) {
            const formattedDates = duplicateDates
                .map(date => formatDateBr(date))
                .join(', ');

            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['ids'],
                message: `Não é possível editar múltiplos slots com a mesma data. Os seguintes dias têm mais de um slot selecionado: ${formattedDates}. Selecione apenas um slot por data.`,
            });
        }
    });

export type ScheduleSlotsUpdateForm = z.infer<typeof scheduleSlotsUpdateSchema>;
