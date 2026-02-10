<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'registration' => 'required|string|max:20|unique:students,registration',
            'period' => ['required', 'integer', 'exists:periods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome pode ter no máximo 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',

            'registration.required' => 'O registro acadêmico é obrigatório.',
            'registration.string' => 'O registro acadêmico deve ser um texto.',
            'registration.max' => 'O registro acadêmico pode ter no máximo 20 caracteres.',
            'registration.unique' => 'Este registro acadêmico já está em uso.',

            'period.required' => 'O período é obrigatório.',
            'period.integer' => 'O período informado é inválido.',
            'period.exists' => 'O período selecionado não existe.',
        ];
    }
}
