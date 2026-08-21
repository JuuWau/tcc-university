<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientScheduleAvailableDaysRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clinic_id' => [
                'required',
                'integer',
                'exists:clinics,id',
            ],
            'period_id' => [
                'required',
                'integer',
                'exists:periods,id',
            ],
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
            'month' => [
                'required',
                'integer',
                'between:1,12',
            ],
            'year' => [
                'required',
                'integer',
                'digits:4',
            ],
        ];
    }
}
