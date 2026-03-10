<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'min:10'],
            'cpf' => [
                'required',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidCPF($value)) {
                        $fail('CPF inválido');
                    }
                },
            ],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'cep' => ['required', 'regex:/^\d{5}-\d{3}$/', 'max:9'],
            'street' => ['required', 'string', 'max:100'],
            'neighborhood' => ['required', 'string', 'max:50'],
            'number' => ['required', 'string', 'max:5'],
            'complement' => ['nullable', 'string', 'max:20'],
            'city' => ['required', 'string'],
            'state' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:50', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome obrigatório',
            'phone.required' => 'Telefone obrigatório',
            'phone.min' => 'Telefone inválido',
            'cpf.required' => 'CPF obrigatório',
            'cpf.regex' => 'CPF inválido',
            'birth_date.required' => 'Data de nascimento obrigatória',
            'birth_date.date' => 'Data de nascimento inválida',
            'birth_date.before_or_equal' => 'Data de nascimento não pode ser maior que a data atual',
            'cep.required' => 'CEP obrigatório',
            'cep.regex' => 'CEP inválido',
            'street.required' => 'Endereço obrigatório',
            'neighborhood.required' => 'Bairro obrigatório',
            'number.required' => 'Número obrigatório',
            'city.required' => 'Cidade obrigatória',
            'state.required' => 'Estado obrigatório',
            'password.required' => 'Senha obrigatória',
            'password.min' => 'Senha deve ter no mínimo 8 caracteres',
            'password.confirmed' => 'As senhas não coincidem',
        ];
    }

    private function isValidCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
