<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $specialty = $this->route('specialty');

        return [
            'name' => ['required', 'string', 'max:100',
                Rule::unique('specialties')
                        ->where(fn ($query) => $query->where(
                            'university_id',
                            auth()->user()->university_id
                        ))
                        ->ignore($specialty),],
            ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da especialidade é obrigatório.',
            'name.string' => 'O nome da especialidade deve ser um texto.',
            'name.max' => 'O nome da especialidade deve ter no máximo 100 caracteres.',
            'name.unique' => 'Já existe uma especialidade com esse nome nesta universidade.',
        ];
    }
}
