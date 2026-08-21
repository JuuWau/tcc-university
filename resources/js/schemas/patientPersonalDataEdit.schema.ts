import { z } from 'zod';

const normalizeOptionalString = (value: unknown) => {
    if (typeof value !== 'string') return value;
    const trimmed = value.trim();
    return trimmed === '' ? null : trimmed;
};

export const patientPersonalDataEditSchema = z.object({
    name: z.preprocess(
        (value) => (typeof value === 'string' ? value.trim() : value),
        z
            .string()
            .min(1, 'Nome é obrigatório')
            .max(255, 'Nome não pode ter mais de 255 caracteres'),
    ),
    patient_type: z.preprocess(
        normalizeOptionalString,
        z.enum(['adulto', 'pediatria'], {
            required_error: 'Tipo do paciente é obrigatório',
            invalid_type_error: 'Tipo do paciente inválido',
        }),
    ),
    email: z.preprocess(
        normalizeOptionalString,
        z
            .string()
            .email('Email inválido')
            .max(255, 'Email não pode ter mais de 255 caracteres')
            .nullable(),
    ),
    phone: z.preprocess(
        normalizeOptionalString,
        z.string().max(20, 'Telefone não pode ter mais de 20 caracteres').nullable(),
    ),
    cpf: z.preprocess(
        normalizeOptionalString,
        z.string().max(14, 'CPF não pode ter mais de 14 caracteres').nullable(),
    ),
    birth_date: z.preprocess(
        normalizeOptionalString,
        z.string().min(10, 'Data de nascimento inválida').nullable()
        .refine((value) => {
            if (!value) return true;

            const [year, month, day] = value.split('-').map(Number);
            const birthDate = new Date(year, month - 1, day);

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            return birthDate <= today;
        }, 'A data de nascimento não pode ser futura.'),
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
        z.string().max(9, 'CEP não pode ter mais de 9 caracteres').nullable(),
    ),
    street: z.preprocess(
        normalizeOptionalString,
        z.string().max(100, 'Rua não pode ter mais de 100 caracteres').nullable(),
    ),
    number: z.preprocess(
        normalizeOptionalString,
        z.string().max(10, 'Número não pode ter mais de 10 caracteres').nullable(),
    ),
    neighborhood: z.preprocess(
        normalizeOptionalString,
        z.string().max(50, 'Bairro não pode ter mais de 50 caracteres').nullable(),
    ),
    city: z.preprocess(
        normalizeOptionalString,
        z.string().max(50, 'Cidade não pode ter mais de 50 caracteres').nullable(),
    ),
    state: z.preprocess(
        normalizeOptionalString,
        z.string().max(2, 'Estado não pode ter mais de 2 caracteres').nullable(),
    ),
    complement: z.preprocess(
        normalizeOptionalString,
        z.string().max(50, 'Complemento não pode ter mais de 50 caracteres').nullable(),
    ),
});

export type PatientPersonalDataEditForm = z.infer<
    typeof patientPersonalDataEditSchema
>;
