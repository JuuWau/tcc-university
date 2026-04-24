<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMultipleScheduleSlotsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $universityId = $this->user()->university_id;

        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:schedule_slots,id'],
            'responsible_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('university_id', $universityId)),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($this->start_time && $value <= $this->start_time) {
                        $fail('O horário de fim deve ser depois do início.');
                    }
                },
            ],
            'available_slots' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Selecione pelo menos um horário.',
            'ids.array' => 'Formato inválido para os horários.',
            'ids.min' => 'Selecione pelo menos um horário.',
            'ids.*.integer' => 'ID de horário inválido.',
            'ids.*.exists' => 'Um ou mais horários não existem.',

            'responsible_id.integer' => 'Responsável inválido.',
            'responsible_id.exists' => 'O responsável selecionado não pertence à universidade.',

            'start_time.required' => 'Informe o horário de início.',
            'start_time.date_format' => 'Formato de horário inválido (use HH:mm).',

            'end_time.date_format' => 'Formato de horário inválido (use HH:mm).',

            'available_slots.integer' => 'As vagas devem ser um número.',
            'available_slots.min' => 'As vagas não podem ser negativas.',
        ];
    }
}
