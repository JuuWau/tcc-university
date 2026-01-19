<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeriodRequest extends FormRequest
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
        $periodId = $this->route('period')->id ?? $this->route('period');

        return [
            'academic_year' => [
                'required',
                'integer',
                'digits:1',
                Rule::unique('periods')
                    ->where(
                        fn($q) =>
                        $q->where('university_id', $this->user()->university_id)
                            ->where('academic_year', $this->academic_year)
                            ->where('semester', $this->semester)
                            ->where('calendar_year', $this->calendar_year)
                    )
                    ->ignore($periodId),
            ],
            'semester' => [
                'required',
                'integer',
                'digits:1',
            ],
            'calendar_year' => [
                'required',
                'integer',
                'digits:4',
            ],
            'specialties' => ['required', 'array', 'min:1'],
            'specialties.*' => ['integer', 'exists:specialties,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year.unique' =>
            'Já existe um período com este ano acadêmico, semestre e ano calendário.',
            'academic_year.required' => 'O ano acadêmico é obrigatório.',
            'academic_year.digits'   => 'O ano acadêmico deve ter 1 dígito.',

            'semester.required'      => 'O semestre é obrigatório.',
            'semester.digits'        => 'O semestre deve ter 1 dígito.',

            'calendar_year.required' => 'O ano calendário é obrigatório.',
            'calendar_year.digits'   => 'O ano calendário deve ter 4 dígitos.',

            'specialties.required'   => 'Selecione pelo menos uma especialidade.',
            'specialties.array'      => 'As especialidades devem ser uma lista válida.',
            'specialties.min'        => 'Selecione pelo menos uma especialidade.',
            'specialties.*.exists'   => 'Uma ou mais especialidades são inválidas.',
        ];
    }
}
