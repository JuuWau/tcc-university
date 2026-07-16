<?php

namespace App\Http\Resources;

use App\Models\ScheduleEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'name' => $this->student?->person?->name,
            'registration' => $this->student?->registration,
            'attended' => $this->status === ScheduleEnrollment::STATUS_ATTENDED,
        ];
    }
}
