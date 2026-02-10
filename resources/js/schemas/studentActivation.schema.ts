import { z } from 'zod';
import { STUDENT_ACTIVATION_REASONS } from '@/constants/studentActivationReason';

export const studentActivationSchema = z
        .object({
                reason: z.enum(
                        Object.keys(STUDENT_ACTIVATION_REASONS) as [
                                keyof typeof STUDENT_ACTIVATION_REASONS,
                                ...(keyof typeof STUDENT_ACTIVATION_REASONS)[]
                        ],
                ),

                note: z.string().nullable().optional(),
        })
        .superRefine((data, ctx) => {
                const reasonConfig = STUDENT_ACTIVATION_REASONS[data.reason];

                if (reasonConfig.requiresNote && !data.note?.trim()) {
                        ctx.addIssue({import { z } from 'zod';
import { cpfSchema } from './studentComplete.schema';

export const studentEditSchema = z
    .object({
        name: z.string().min(1, 'Nome é obrigatório').max(255, 'Nome muito longo'),
        email: z.string().email('Email inválido'),
        phone: z.string().min(10, 'Telefone inválido'),
        cpf: cpfSchema,
        birth_date: z
            .string()
            .min(10, 'Data de nascimento inválida')
            .refine(
                (date) => {
                    const today = new Date();
                    const birth = new Date(date);
                    return birth <= today;
                },
                { message: 'Data de nascimento não pode ser maior que a data atual' }
            ),
        cep: z
            .string()
            .regex(/^\d{5}-\d{3}$/, 'CEP inválido')
            .max(9, 'CEP não pode ter mais de 9 caracteres'),
        street: z
            .string()
            .min(1, 'Endereço obrigatório')
            .max(100, 'Rua não pode ter mais de 100 caracteres'),
        neighborhood: z
            .string()
            .min(1, 'Bairro obrigatório')
            .max(50, 'Bairro não pode ter mais de 50 caracteres'),
        number: z
            .string()
            .min(1, 'Número obrigatório')
            .max(5, 'Número não pode ter mais de 5 caracteres'),
        complement: z.string().max(20, 'Complemento não pode ter mais de 20 caracteres').optional().nullable(),
        city: z.string().min(1, 'Cidade obrigatória'),
        state: z.string().min(1, 'Estado obrigatório'),
        password: z
            .string()
            .max(50, 'Senha não pode ter mais de 50 caracteres')
            .optional()
            .nullable()
            .refine(
                (v) => v == null || v === '' || v.length >= 8,
                'Senha deve ter no mínimo 8 caracteres'
            ),
    });

export type StudentEditForm = z.infer<typeof studentEditSchema>;

                                path: ['note'],
                                message: 'Descrição do motivo é obrigatória',
                                code: z.ZodIssueCode.custom,
                        });
                }
        });

export type StudentActivationSchema = z.infer<
        typeof studentActivationSchema
>;