<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicManagementPrePatientResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'pre_patient_id' => $this->pre_patient_id,
			'name' => $this->prePatient?->name,
			'cpf' => $this->prePatient?->cpf,
			'phone' => $this->prePatient?->phone,
			'status' => 'waiting',
			'enrolled_at' => $this->enrolled_at,
		];
	}
}