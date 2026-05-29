<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentScheduleEventResource extends JsonResource
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
            'date' => $this->date
                ->format('Y-m-d'),
            'time' => substr(
                $this->start_time,
                0,
                5,
            ),
            'end_time' => substr(
                $this->end_time,
                0,
                5,
            ),
        ];
    }
}
