<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateStudentRequest extends FormRequest
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
                    'returned_from_leave',
                    'administrative_correction',
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
            'reason.required' => 'O motivo da ativação é obrigatório.',
            'reason.in' => 'Motivo de ativação inválido.',
            'note.required' => 'Descreva o motivo da ativação.',
            'note.max' => 'A descrição pode ter no máximo 1000 caracteres.',
        ];
    }
}
