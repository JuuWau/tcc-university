<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTableResource extends JsonResource
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
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'person' => [
                'name' => $this->person?->name,
            ],
            'role' => [
                'id' => $this->roles->first()?->id,
                'name' => $this->roles->first()?->name,
                'slug' => $this->roles->first()?->slug,
            ],
            'invite' => [
                'used_at' => $this->invite?->used_at,
                'expires_at' => $this->invite?->expires_at,
            ],
            'deleted_at' => $this->deleted_at,
        ];
    }
}
