<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientStudentDataRequest extends FormRequest
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
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'status' => ['required', 'string', 'in:ativo,inativo,tratamento,pausa_tratamento,abandono,concluido,transferencia'],
        ];
    }
}
