<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
            'patient_id' => ['required', 'exists:patients,id'],
            'procedure_id' => ['nullable', 'exists:procedures,id'],
            'status' => [
                'required',
                'in:scheduled,confirmed,completed,canceled,no_show,rescheduled',
            ],
            'scheduled_start_at' => ['required', 'date', 'after_or_equal:today',],
            'scheduled_end_at' => ['required', 'date', 'after:scheduled_start_at', 'after:scheduled_start_at',],
            'notes' => ['nullable', 'string'],
        ];
    }
}
