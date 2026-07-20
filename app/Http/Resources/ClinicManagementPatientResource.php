<?php

namespace App\Http\Resources;

use App\Models\ClinicWaitingList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicManagementPatientResource extends JsonResource
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
            'code' => $this->patient?->code,
            'patient_id' => $this->patient_id,
            'name' => $this->patient?->name,
            'cpf' => $this->patient?->cpf,
            'phone' => $this->patient?->phone,
            'status' => $this->resource instanceof ClinicWaitingList
                ? 'waiting'
                : 'enrolled',
            'enrolled_at' => $this->enrolled_at
        ];
    }
}
