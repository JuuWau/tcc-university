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
            'periods' => $this->whenLoaded('periods', function () {
                return $this->periods->map(fn($period) => [
                    'id' => $period->id,
                    'academic_year' => $period->academic_year,
                    'semester' => $period->semester,
                    'calendar_year' => $period->calendar_year,
                    'pivot' => [
                        'started_at' => $period->pivot->started_at,
                        'ended_at' => $period->pivot->ended_at,
                        'is_current' => $period->pivot->is_current,
                    ],
                ]);
            }),
        ];
    }
}
