import { z } from 'zod';
import { cpfSchema } from './accessComplete.schema';

export const userSchema = z.object({
    name: z.string().min(1, 'Nome é obrigatório'),
    email: z.string().email('Email inválido'),
    role_id: z
        .number()
        .nullable()
        .refine((val) => val !== null && val >= 1, { message: 'Selecione um perfil' }),
});

export const userPersonalDataEditSchema = z
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

export const userRoleEditSchema = z.object({
    role_id: z.number({ required_error: 'Selecione um perfil' }).min(1, 'Selecione um perfil'),
});
