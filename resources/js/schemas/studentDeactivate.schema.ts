import { z } from 'zod';
import { STUDENT_REASONS } from '@/constants/studentReason';

export const studentDeactivateSchema = z
    .object({
        reason: z.enum(
            Object.keys(STUDENT_REASONS) as [
                keyof typeof STUDENT_REASONS,
                ...(keyof typeof STUDENT_REASONS)[]
            ],
        ),

        note: z.string().nullable().optional(),
    })
    .superRefine((data, ctx) => {
        const reasonConfig = STUDENT_REASONS[data.reason];

        if (reasonConfig.requiresNote && !data.note?.trim()) {
            ctx.addIssue({
                path: ['note'],
                message: 'Descrição do motivo é obrigatória',
                code: z.ZodIssueCode.custom,
            });
        }
    });

export type StudentDeactivateSchema = z.infer<
    typeof studentDeactivateSchema
>;