import { z } from 'zod';

function isValidCPF(cpf: string): boolean {
  cpf = cpf.replace(/\D/g, '');

  if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

  const calcDigit = (cpfPart: string, factor: number) => {
    let total = 0;
    for (let i = 0; i < cpfPart.length; i++) {
      total += Number(cpfPart[i]) * factor--;
    }
    const mod = total % 11;
    return mod < 2 ? 0 : 11 - mod;
  };

  const digit1 = calcDigit(cpf.substring(0, 9), 10);
  const digit2 = calcDigit(cpf.substring(0, 10), 11);

  return digit1 === Number(cpf[9]) && digit2 === Number(cpf[10]);
}

export const cpfSchema = z
  .string()
  .regex(/^\d{3}\.\d{3}\.\d{3}-\d{2}$/, 'CPF inválido')
  .refine(isValidCPF, 'CPF inválido');

export const studentCompleteSchema = z.object({
  email: z.string().email('Email inválido'),
  phone: z.string().min(10, 'Telefone inválido'),
  cpf: z.string().regex(/^\d{3}\.\d{3}\.\d{3}-\d{2}$/, 'CPF inválido').max(100, 'CPF não pode ter mais de 14 caracteres'),
  birth_date: z
    .string()
    .min(10, 'Data de nascimento inválida')
    .refine((date) => {
      const today = new Date();
      const birth = new Date(date);
      return birth <= today;
    }, { message: 'Data de nascimento não pode ser maior que a data atual' }),
  cep: z.string().regex(/^\d{5}-\d{3}$/, 'CEP inválido').max(100, 'CEP não pode ter mais de 9 caracteres'),
  street: z.string().min(1, 'Endereço obrigatório').max(100, 'Rua não pode ter mais de 100 caracteres'),
  neighborhood: z.string().min(1, 'Bairro obrigatório').max(50, 'Bairro não pode ter mais de 50 caracteres'),
  number: z.string().min(1, 'Número obrigatório').max(100, 'Número não pode ter mais de 5 caracteres'),
  complement: z.string().max(20, 'Complemento não pode ter mais de 20 caracteres').optional(),
  city: z.string().min(1, 'Cidade obrigatória'),
  state: z.string().min(1, 'Estado obrigatório'),
  password: z.string().min(8, 'Senha deve ter no mínimo 8 caracteres').max(50, 'Rua não pode ter mais de 50 caracteres'),
  passwordConfirmation: z.string().min(1, 'Confirmação de senha obrigatória'),
}).refine((data) => data.password === data.passwordConfirmation, {
  message: 'As senhas não coincidem',
  path: ['passwordConfirmation'],
});
