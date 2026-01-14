<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da especialidade é obrigatório.',
            'name.string' => 'O nome da especialidade deve ser um texto.',
            'name.max' => 'O nome da especialidade deve ter no máximo 100 caracteres.',
        ];
    }
}
