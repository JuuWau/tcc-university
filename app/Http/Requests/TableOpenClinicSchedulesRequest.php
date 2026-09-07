<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableOpenClinicSchedulesRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'period_id' => ['nullable', 'integer', 'exists:periods,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'date' => ['nullable', 'date'],
            'sort_field' => ['sometimes', 'string', 'in:date,start_time,end_time,created_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
