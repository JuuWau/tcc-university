<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableClinicRequest extends FormRequest
{
        public function authorize(): bool
        {
                return true;
        }

        public function rules(): array
        {
                return [
                        'page' => ['sometimes', 'integer', 'min:1'],
                        'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
                        'sort_field' => ['sometimes', 'string', 'in:name,created_at'],
                        'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
                        'status' => ['sometimes', 'string', 'in:all,active,inactive'],
                        'search' => ['nullable', 'string', 'max:255'],
                ];
        }
}
