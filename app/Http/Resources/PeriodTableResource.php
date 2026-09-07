<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodTableResource extends JsonResource
{
        public function toArray(Request $request): array
        {
                return [
                        'id' => $this->id,
                        'academic_year' => $this->academic_year,
                        'semester' => $this->semester,
                        'calendar_year' => $this->calendar_year,
                        'specialties' => SpecialtyResource::collection($this->whenLoaded('specialties')),
                        'created_at' => $this->created_at,
                        'updated_at' => $this->updated_at,
                ];
        }
}
