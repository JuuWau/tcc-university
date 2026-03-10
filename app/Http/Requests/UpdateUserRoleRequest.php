<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
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
            'role_id.required' => 'Perfil é obrigatório.',
            'role_id.exists' => 'Perfil selecionado é inválido.',
            'role_id.not_in' => 'Não é permitido atribuir perfil de estudante a colaboradores.',
        ];
    }
}
