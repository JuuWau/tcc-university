<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcedureTableResource extends JsonResource
{
        public function toArray(Request $request): array
        {
                return [
                        'id' => $this->id,
                        'name' => $this->name,
                        'specialty_id' => $this->specialty_id,
                        'specialty' => new SpecialtyResource($this->whenLoaded('specialty')),
                        'created_at' => $this->created_at,
                        'updated_at' => $this->updated_at,
                ];
        }
}
