<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('clinics')
                    ->where(fn ($query) => $query
                        ->where('university_id', $this->user()->university_id)
                        ->whereNull('deleted_at')),
            ],
            'specialty_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'specialty_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('specialties', 'id')
                    ->where(fn ($query) => $query
                        ->where('university_id', $this->user()->university_id)
                        ->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da clínica é obrigatório.',
            'name.string' => 'O nome da clínica deve ser um texto.',
            'name.max' => 'O nome da clínica deve ter no máximo 120 caracteres.',
            'name.unique' => 'Já existe uma clínica com esse nome.',
            'specialty_ids.required' => 'A especialidade é obrigatória.',
            'specialty_ids.array' => 'As especialidades devem ser informadas em uma lista.',
            'specialty_ids.min' => 'Selecione pelo menos uma especialidade.',
            'specialty_ids.*.integer' => 'A especialidade deve ser um número inteiro.',
            'specialty_ids.*.distinct' => 'Não é permitido selecionar a mesma especialidade mais de uma vez.',
            'specialty_ids.*.exists' => 'Uma das especialidades selecionadas não existe.',
        ];
    }
}
