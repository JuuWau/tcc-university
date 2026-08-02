<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionLogResource extends JsonResource
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
            'module' => $this->module,
            'action' => $this->action,
            'description' => $this->description,
            'model' => class_basename($this->model_type),
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'changes' => $this->changes,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->person?->name,
            ],
        ];
    }
}
