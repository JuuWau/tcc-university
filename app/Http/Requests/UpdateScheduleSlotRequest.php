<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleSlotRequest extends FormRequest
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
        $universityId = $this->user()->university_id;

        return [
            'period_id' => [
                'required',
                'integer',
                Rule::exists('periods', 'id')->where(fn ($query) => $query
                    ->where('university_id', $universityId)),
            ],
            'responsible_id' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('university_id', $universityId)),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'available_slots' => ['integer', 'min:0'],
            'allow_student_booking' => ['boolean'],
            'allow_student_enrollment' => ['boolean'],
            'allow_procedure_booking' => ['boolean'],
        ];
    }
}
