<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        'registration' => [
            'required',
            'string',
            'max:255',
            Rule::unique('students', 'registration')
                ->ignore($this->route('student')),
        ],
        'period' => [
            'required',
            'integer',
            'exists:periods,id',
        ],
    ];
    }

    public function messages(): array
    {
        return [
            'registration.required' => 'Registro acadêmico é obrigatório.',
            'registration.max' => 'Registro acadêmico não pode ter mais de 255 caracteres.',
            'registration.unique' => 'Registro acadêmico já está em uso.',
            'period.required' => 'Período é obrigatório.',
            'period.exists' => 'Período selecionado é inválido.',
        ];
    }
}
