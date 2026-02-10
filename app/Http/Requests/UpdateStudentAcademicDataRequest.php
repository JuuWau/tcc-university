<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentAcademicDataRequest extends FormRequest
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
            'registration' => ['required', 'string', 'max:255'],
            'period' => ['required', 'integer', 'exists:periods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration.required' => 'Registro acadêmico é obrigatório.',
            'registration.max' => 'Registro acadêmico não pode ter mais de 255 caracteres.',
            'period.required' => 'Período é obrigatório.',
            'period.exists' => 'Período selecionado é inválido.',
        ];
    }
}
