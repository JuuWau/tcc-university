<?php

namespace App\Http\Requests;

use App\Models\PrePatient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrePatientTableRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                'string',
                Rule::in(PrePatient::statuses()),
            ],
            'clinic_id' => [
                'nullable',
                'integer',
                'exists:clinics,id',
            ],
            'sort_field' => [
                'nullable',
                'string',
                Rule::in([
                    'name',
                    'created_at',
                    'status',
                ]),
            ],
            'sort_dir' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
}
