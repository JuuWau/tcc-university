<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $clinicId = $this->route('clinic')->id ?? $this->route('clinic');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('clinics')
                    ->where(fn ($query) => $query
                        ->where('university_id', $this->user()->university_id)
                        ->whereNull('deleted_at'))
                    ->ignore($clinicId),
            ],
        ];
    }
}
