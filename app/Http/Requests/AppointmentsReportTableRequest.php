<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AppointmentsReportTableRequest extends FormRequest
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
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'sort_field' => [
                'nullable',
                'string',
                Rule::in([
                    'scheduled_start_at',
                    'scheduled_end_at',
                    'status',
                    'created_at',
                ]),
            ],
            'sort_dir' => [
                'nullable',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],
            'clinic_id' => [
                'nullable',
                'integer',
                'exists:clinics,id',
            ],
            'responsible_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'student_id' => [
                'nullable',
                'integer',
                'exists:students,id',
            ],
            'patient_id' => [
                'nullable',
                'integer',
                'exists:patients,id',
            ],
            'period_id' => [
                'nullable',
                'integer',
                'exists:periods,id',
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'scheduled',
                    'confirmed',
                    'completed',
                    'canceled',
                    'no_show',
                    'rescheduled',
                ]),
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $startDate = $this->input('start_date');
                $endDate = $this->input('end_date');

                if (!$startDate || !$endDate) {
                    return;
                }

                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);

                $maxDate = $start->copy()->addYear();

                if ($end->greaterThan($maxDate)) {
                    $validator->errors()->add(
                        'end_date',
                        'O período máximo para consulta é de 12 meses.'
                    );
                }
            },
        ];
    }
}
