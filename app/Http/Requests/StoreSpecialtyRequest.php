<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:specialties,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da especialidade é obrigatório.',
            'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'name.unique' => 'Essa especialidade já existe.',
        ];
    }
}
