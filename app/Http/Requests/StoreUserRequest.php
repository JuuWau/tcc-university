<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
                Rule::notIn([Role::STUDENT]),
            ],
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

            'role_id.required' => 'O perfil é obrigatório.',
            'role_id.integer' => 'O perfil informado é inválido.',
            'role_id.exists' => 'O perfil selecionado não existe.',
            'role_id.not_in' => 'Não é permitido cadastrar usuário com perfil de estudante.',
        ];
    }
}
