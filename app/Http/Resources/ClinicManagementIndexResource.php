<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicManagementIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'clinic_id' => $this->id,
            'clinic_name' => $this->name,
            'active_patients_count' => $this->active_patients_count,
            'waiting_patients_count' => $this->waiting_patients_count,
        ];
    }
}
