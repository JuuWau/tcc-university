<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentsReportTableRequest extends FormRequest
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
            'period_id' => [
                'nullable',
                'integer',
                Rule::exists('periods', 'id'),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
            'invitation_status' => [
                'nullable',
                'string',
                Rule::in([
                    'accepted',
                    'pending',
                    'declined',
                ]),
            ],
            'sort_field' => [
                'nullable',
                'string',
                Rule::in([
                    'name',
                    'registration',
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
