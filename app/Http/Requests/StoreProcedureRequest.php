<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->university_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('procedures')
                    ->where('university_id', $userId)
                    ->where('specialty_id', $this->input('specialty_id'))
                    ->whereNull('deleted_at'),
            ],
            'specialty_id' => ['required', 'integer', 'exists:specialties,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do procedimento é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'name.unique' => 'Já existe um procedimento com este nome nesta especialidade.',
            'specialty_id.required' => 'A especialidade é obrigatória.',
            'specialty_id.exists' => 'Especialidade selecionada é inválida.',
        ];
    }
}
