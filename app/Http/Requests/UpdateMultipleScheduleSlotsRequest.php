<?php

namespace App\Http\Requests;

use App\Models\ScheduleSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'responsible_ids' => [
                'nullable',
                'array',
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
            'allow_student_booking' => ['required', 'boolean'],
            'allow_student_enrollment' => ['required', 'boolean'],
            'allow_procedure_booking' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $ids = $this->input('ids', []);
            
            if (empty($ids)) {
                return;
            }

            $slots = ScheduleSlot::whereIn('id', $ids)
                ->select('id', 'date')
                ->get();

            $dates = $slots->groupBy('date');
            
            $duplicateDates = $dates->filter(fn ($group) => $group->count() > 1);

            if ($duplicateDates->isNotEmpty()) {
                $formattedDates = $duplicateDates->keys()->map(function ($date) {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y');
                })->implode(', ');

                $validator->errors()->add(
                    'ids', 
                    "Não é possível editar múltiplos slots com a mesma data. " .
                    "Os seguintes dias têm mais de um slot selecionado: {$formattedDates}. " .
                    "Selecione apenas um slot por data."
                );
            }
        });
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

            'allow_student_booking.required' => 'Informe se o agendamento de estudantes é permitido.',
            'allow_student_booking.boolean' => 'O valor de agendamento de estudantes deve ser verdadeiro ou falso.',

            'allow_student_enrollment.required' => 'Informe se a matrícula de estudantes é permitida.',
            'allow_student_enrollment.boolean' => 'O valor de matrícula de estudantes deve ser verdadeiro ou falso.',
            
            'allow_procedure_booking.required' => 'Informe se o agendamento de procedimentos é permitido.',
            'allow_procedure_booking.boolean' => 'O valor de agendamento de procedimentos deve ser verdadeiro ou falso.',
        ];
    }
}
