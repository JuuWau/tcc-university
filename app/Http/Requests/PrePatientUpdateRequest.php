<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrePatientUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'birth_date' => ['nullable', 'date'],
            'biological_sex' => ['required', 'string', 'in:male,female'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'patient_type' => ['required', 'string', 'in:adult,pediatric'],
        ];
    }
}