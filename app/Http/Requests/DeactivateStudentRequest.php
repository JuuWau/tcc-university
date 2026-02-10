<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeactivateStudentRequest extends FormRequest
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
            'reason' => [
                'required',
                'string',
                Rule::in([
                    'leave_of_absence',
                    'transfer',
                    'withdrawal',
                    'graduation',
                    'delinquency',
                    'administrative',
                    'other',
                ]),
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn() => $this->input('reason') === 'other'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'O motivo da inativação é obrigatório.',
            'reason.in' => 'Motivo de inativação inválido.',
            'note.required' => 'Descreva o motivo da inativação.',
            'note.max' => 'A descrição pode ter no máximo 1000 caracteres.',
        ];
    }
}
