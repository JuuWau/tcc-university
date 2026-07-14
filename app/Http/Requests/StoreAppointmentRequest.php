<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'schedule_enrollment_id' => [
                'required',
                'integer',
                'exists:schedule_enrollments,id',
            ],
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,id',
            ],
            'procedure_id' => [
                'nullable',
                'integer',
                'exists:procedures,id',
            ],
            'status' => [
                'required',
                'in:scheduled,confirmed,completed,canceled,no_show,rescheduled',
            ],
            'scheduled_start_at' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'scheduled_end_at' => [
                'required',
                'date',
                'after:scheduled_start_at',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
