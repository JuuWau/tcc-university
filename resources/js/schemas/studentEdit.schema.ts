import { z } from 'zod';
import { cpfSchema } from './accessComplete.schema';

export const studentEditSchema = z
    .object({
        name: z.string().min(1, 'Nome é obrigatório').max(255, 'Nome muito longo'),
        email: z.string().email('Email inválido'),
        phone: z.string().min(10, 'Telefone inválido'),
        cpf: cpfSchema,
        birth_date: z
            .string()
            .min(10, 'Data de nascimento inválida')
            .refine((value) => {
                if (!value) return true;

                const [year, month, day] = value.split('-').map(Number);
                const birthDate = new Date(year, month - 1, day);

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                return birthDate <= today;
            }, 'A data de nascimento não pode ser futura.'),
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
        state: z
            .string({
                required_error: 'Estado obrigatório',
                invalid_type_error: 'Estado obrigatório',
            })
            .min(1, 'Estado obrigatório'),
        city: z
            .string({
                required_error: 'Cidade obrigatória',
                invalid_type_error: 'Cidade obrigatória',
            })
            .min(1, 'Cidade obrigatória'),
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
