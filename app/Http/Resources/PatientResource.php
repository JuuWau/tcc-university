<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'university_id' => $this->university_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cpf' => $this->cpf,
            'code' => $this->code,
            'status' => $this->status,
            'patient_type' => $this->patient_type,
            'birth_date' => $this->birth_date,
            'students' => $this->whenLoaded('students', function () {
                return $this->students->map(fn ($student) => [
                    'id' => $student->id,
                    'name' => $student->person?->name ?? '—',
                ])->values();
            }),
            'student_ids' => $this->whenLoaded('students', function () {
                return $this->students
                    ->pluck('id')
                    ->values();
            }),
            'address' => $this->whenLoaded('address', function () {
                return $this->address;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
