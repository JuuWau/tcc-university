<?php

namespace App\Http\Resources;

use App\Models\ClinicWaitingList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientClinicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isWaiting = $this->resource instanceof ClinicWaitingList;
        
        return [
            'clinic_id' => $this->clinic_id,
            'clinic_name' => $this->clinic?->name,
            'period_name' => null,
            'status' => $isWaiting
                ? 'waiting'
                : 'enrolled',
            'enrolled_at' => $this->enrolled_at,
            'created_at' => $this->created_at
        ];
    }
}
