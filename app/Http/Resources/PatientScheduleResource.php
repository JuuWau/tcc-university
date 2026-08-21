<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $scheduleSlot = $this->enrollment?->slot;

        return [
            'id' => $this->id,
            'date' => $this->scheduled_start_at?->format('d/m/Y'),
            'start_time' => $this->scheduled_start_at?->format('H:i'),
            'end_time' => $this->scheduled_end_at?->format('H:i'),
            'status' => $this->status,
            'clinic' => $scheduleSlot?->clinic
                ? [
                    'id' => $scheduleSlot->clinic->id,
                    'name' => $scheduleSlot->clinic->name,
                ]
                : null,
            'period' => $scheduleSlot?->period
                ? [
                    'id' => $scheduleSlot->period->id,
                    'academic_year' => $scheduleSlot->period->academic_year,
                    'semester' => $scheduleSlot->period->semester,
                    'calendar_year' => $scheduleSlot->period->calendar_year,
                    'name' => sprintf(
                        '%dº ano %dº semestre de %d',
                        $scheduleSlot->period->academic_year,
                        $scheduleSlot->period->semester,
                        $scheduleSlot->period->calendar_year,
                    ),
                ]
                : null,
            'student' => $this->student
                ? [
                    'id' => $this->student->id,
                    'name' => $this->student->person?->name,
                ]
                : null,
            'procedure' => $this->procedure
                ? [
                    'id' => $this->procedure->id,
                    'name' => $this->procedure->name,
                ]
                : null,
            'responsibles' => $scheduleSlot?->responsibles
                ?->map(fn ($responsible) => [
                    'id' => $responsible->id,
                    'name' => $responsible->person?->name,
                ])
                ->values()
                ->all() ?? [],
        ];
    }
}
