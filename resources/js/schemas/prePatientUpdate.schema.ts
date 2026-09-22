import { z } from 'zod';

export const prePatientUpdateSchema = z.object({
	name: z
		.string()
		.min(1, 'O nome é obrigatório')
		.max(
			255,
			'O nome deve ter no máximo 255 caracteres',
		),
	cpf: z.string().nullable(),
	birth_date: z.string().nullable(),
	biological_sex: z.enum(['male', 'female'], {
		message: 'O sexo biológico é obrigatório',
	}),
	phone: z.string().nullable(),
	email: z.preprocess(
		(value) => value === '' ? null : value,
		z.string().email('Informe um e-mail válido').nullable(),
	),
	patient_type: z.enum(['adult', 'pediatric'], {
		message: 'O tipo de paciente é obrigatório',
	}),
});

export type PrePatientUpdateData = z.infer<
	typeof prePatientUpdateSchema
>;