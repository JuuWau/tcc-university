<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientsReportTableRequest extends FormRequest
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
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'patient_type' => [
                'nullable',
                'string',
                Rule::in([
                    'pediatria',
                    'adulto',
                ]),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(Patient::statuses()),
            ],
            'sort_field' => [
                'nullable',
                'string',
                Rule::in([
                    'name',
                    'code',
                    'cpf',
                    'phone',
                    'birth_date',
                    'patient_type',
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
        ];
    }
}
