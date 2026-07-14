<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'date' => $this->scheduled_start_at?->format('Y-m-d'),
            'start_time' => $this->scheduled_start_at?->format('H:i'),
            'end_time' => $this->scheduled_end_at?->format('H:i'),
            'patient' => $this->patient?->name,
            'patient_id' => $this->patient_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'procedure_id' => $this->procedure_id,
            'procedure' => $this->procedure?->name,
            'allow_procedure_booking' => $this->enrollment?->slot?->allow_procedure_booking,
            'schedule_enrollment_id' => $this->schedule_enrollment_id,
        ];
    }
}
