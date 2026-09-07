<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceDateResource extends JsonResource
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
            'date' => $this->date,
            'label' => $this->date->format('d/m/Y'),
            'editable' => auth()->user()->hasRole(Role::ADMIN) || $this->date->isToday(),
        ];
    }
}
