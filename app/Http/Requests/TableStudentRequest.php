<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableStudentRequest extends FormRequest
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
            'sort_field' => ['sometimes', 'string', 'in:person.name,registration,created_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
            'status' => ['sometimes', 'string', 'in:all,pending,active,inactive'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
