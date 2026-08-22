import { z } from 'zod';

const normalizeOptionalString = (value: unknown) => {
    if (typeof value !== 'string') return value;
    const trimmed = value.trim();
    return trimmed === '' ? null : trimmed;
};

export const patientCreateSchema = z.object({
    code: z.preprocess(
        (value) => (typeof value === 'string' ? value.trim() : value),
        z
            .string()
            .min(1, 'Código é obrigatório')
            .max(50, 'Código não pode ter mais de 50 caracteres'),
    ),
    name: z.preprocess(
        (value) => (typeof value === 'string' ? value.trim() : value),
        z
            .string()
            .min(1, 'Nome é obrigatório')
            .max(255, 'Nome não pode ter mais de 255 caracteres'),
    ),
    email: z.preprocess(
        normalizeOptionalString,
        z
            .string()
            .email('Email inválido')
            .max(255, 'Email não pode ter mais de 255 caracteres')
            .optional()
            .nullable(),
    ),
    student_ids: z.array(z.number({
            invalid_type_error: 'Estudante inválido',
        }))
        .min(0, 'Estudante inválido')
        .optional()
        .nullable(),
    cpf: z.preprocess(
        normalizeOptionalString,
        z.string().max(14, 'CPF não pode ter mais de 14 caracteres').optional().nullable(),
    ),
    phone: z.preprocess(
        normalizeOptionalString,
        z.string().max(20, 'Telefone não pode ter mais de 20 caracteres').optional().nullable(),
    ),
    birth_date: z.preprocess(
        normalizeOptionalString,
        z.string().min(10, 'Data de nascimento inválida').optional().nullable(),
    ),
    biological_sex: z.preprocess(
        normalizeOptionalString,
        z.enum(['male', 'female'], {
            required_error: 'Sexo biológico é obrigatório',
            invalid_type_error: 'Sexo biológico inválido',
        }),
    ),
    cep: z.preprocess(
        normalizeOptionalString,
        z.string().max(9, 'CEP não pode ter mais de 9 caracteres').optional().nullable(),
    ),
    street: z.preprocess(
        normalizeOptionalString,
        z.string().max(100, 'Rua não pode ter mais de 100 caracteres').optional().nullable(),
    ),
    number: z.preprocess(
        normalizeOptionalString,
        z.string().max(10, 'Número não pode ter mais de 10 caracteres').optional().nullable(),
    ),
    neighborhood: z.preprocess(
        normalizeOptionalString,
        z.string().max(50, 'Bairro não pode ter mais de 50 caracteres').optional().nullable(),
    ),
    city: z.preprocess(
        normalizeOptionalString,
        z.string().max(50, 'Cidade não pode ter mais de 50 caracteres').optional().nullable(),
    ),
    state: z.preprocess(
        normalizeOptionalString,
        z.string().max(2, 'Estado não pode ter mais de 2 caracteres').optional().nullable(),
    ),
    complement: z.preprocess(
        normalizeOptionalString,
        z.string().max(50, 'Complemento não pode ter mais de 50 caracteres').optional().nullable(),
    ),
    patient_type: z
        .enum(['adulto', 'pediatria'])
        .nullable()
        .refine((value) => value !== null, {
            message: 'Tipo de atendimento é obrigatório',
        }),
});

export type PatientCreateForm = z.infer<typeof patientCreateSchema>;
