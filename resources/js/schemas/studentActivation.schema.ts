import { z } from 'zod';
import { STUDENT_ACTIVATION_REASONS } from '@/constants/studentActivationReason';

export const studentActivationSchema = z
    .object({
        reason: z.enum(
            Object.keys(STUDENT_ACTIVATION_REASONS) as [
                keyof typeof STUDENT_ACTIVATION_REASONS,
                ...(keyof typeof STUDENT_ACTIVATION_REASONS)[],
            ],
        ),
        note: z.string().nullable().optional(),
    })
    .superRefine((data, ctx) => {
        const reasonConfig = STUDENT_ACTIVATION_REASONS[data.reason];

        if (reasonConfig.requiresNote && !data.note?.trim()) {
            ctx.addIssue({
                path: ['note'],
                message: 'Descrição do motivo é obrigatória',
                code: z.ZodIssueCode.custom,
            });
        }
    });

export type StudentActivationSchema = z.infer<
    typeof studentActivationSchema
>;
