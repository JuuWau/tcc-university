<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
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
        $patient = $this->route('patient');
        $patientId = $patient instanceof Patient ? $patient->id : $patient;

        return [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('patients', 'code')->ignore($patientId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('patients', 'cpf')->ignore($patientId)],
            'birth_date' => ['nullable', 'date'],
            'cep' => ['nullable', 'string', 'max:9'],
            'street' => ['nullable', 'string', 'max:100'],
            'number' => ['nullable', 'string', 'max:10'],
            'neighborhood' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:50'],
            'state' => ['nullable', 'string', 'max:2'],
            'complement' => ['nullable', 'string', 'max:50'],
            'patient_type' => ['sometimes', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => 'Este CPF já está cadastrado.',
        ];
    }
}
