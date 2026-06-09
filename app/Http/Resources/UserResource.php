<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = $request->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'web_roles' => $this->whenLoaded('webRoles', fn () => $this->webRoles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])),
            'can' => [
                'view' => $authUser?->can('view', $this->resource) ?? false,
                'update' => $authUser?->can('update', $this->resource) ?? false,
                'delete' => $authUser?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
