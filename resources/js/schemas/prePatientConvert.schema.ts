import { z } from 'zod';

export const prePatientConvertSchema = z.object({
	code: z
		.string()
		.min(1, 'O código do paciente é obrigatório')
		.max(
			255,
			'O código do paciente deve ter no máximo 255 caracteres',
		),
});
