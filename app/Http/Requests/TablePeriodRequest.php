<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TablePeriodRequest extends FormRequest
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
                        'sort_field' => ['sometimes', 'string', 'in:academic_year,semester,calendar_year,created_at'],
                        'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
                        'search' => ['nullable', 'string', 'max:255'],
                ];
        }
}
