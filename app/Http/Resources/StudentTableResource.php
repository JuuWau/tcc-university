<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentTableResource extends JsonResource
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
            'registration' => $this->registration,
            'person' => [
                'name' => $this->person?->name,
                'cpf' => $this->person?->cpf,
                'phone' => $this->person?->phone,
            ],
            'email' => $this->user?->email,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
