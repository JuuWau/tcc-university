<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentConfirmationResource extends JsonResource
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
            'scheduled_start_at' => $this->scheduled_start_at,
            'scheduled_end_at' => $this->scheduled_end_at,
            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->person?->name,
            ],
            'clinic' => [
                'id' => $this->enrollment?->slot?->clinic?->id,
                'name' => $this->enrollment?->slot?->clinic?->name,
            ],
            'period' => [
                'id' => $this->enrollment?->slot?->period?->id,
                'name' => sprintf(
                    '%sº ano - %sº semestre - %s',
                    $this->enrollment?->slot?->period?->academic_year,
                    $this->enrollment?->slot?->period?->semester,
                    $this->enrollment?->slot?->period?->calendar_year
                ),
            ],
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
                'phone' => $this->patient?->phone,
                'code' => $this->patient?->code,
            ],
            'status' => $this->status,
        ];
    }
}
