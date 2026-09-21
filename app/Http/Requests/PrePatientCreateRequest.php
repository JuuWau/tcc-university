<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PrePatientCreateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'cpf' => [
                'nullable',
                'string',
                'max:14',
            ],
            'birth_date' => [
                'nullable',
                'date',
            ],
            'biological_sex' => [
                'required',
                'string',
                'in:male,female',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'patient_type' => [
                'required',
                'string',
                'in:adult,pediatric',
            ],
        ];
    }
}
