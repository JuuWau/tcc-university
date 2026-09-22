<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrePatientTableResource extends JsonResource
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
            'name' => $this->name,
            'cpf' => $this->cpf,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'biological_sex' => $this->biological_sex,
            'patient_type' => $this->patient_type,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'clinics' => $this->whenLoaded(
                'waitingLists',
                fn() => $this->waitingLists->map(fn($waitingList) => [
                    'id' => $waitingList->clinic->id,
                    'name' => $waitingList->clinic->name,
                    'enrolled_at' => $waitingList->enrolled_at?->format('Y-m-d'),
                ])
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
