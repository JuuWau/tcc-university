<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpenScheduleRequest extends FormRequest
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
            'clinic_id' => [
                'required',
                'integer',
                Rule::exists('clinics', 'id')->where(fn ($query) => $query
                    ->where('university_id', $this->user()->university_id)
                    ->where('active', true)
                    ->whereNull('deleted_at')),
            ],
            'available_slots' => ['nullable', 'integer', 'min:0'],
            'allow_student_booking' => ['required', 'boolean'],
            'allow_student_enrollment' => ['required', 'boolean'],
            'allow_procedure_booking' => ['required', 'boolean'],
            'period_id' => ['required', 'integer', 'exists:periods,id'],
            'responsible_ids' => ['array'],
            'responsible_ids.*' => ['exists:users,id'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }
}
