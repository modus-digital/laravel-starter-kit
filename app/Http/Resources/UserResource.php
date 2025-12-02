<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'status' => $this->resource->status,
            'status_label' => $this->resource->status->getLabel(),
            'email_verified_at' => $this->resource->email_verified_at,
            'provider' => $this->resource->provider,
            'roles' => $this->whenLoaded('roles', fn () => $this->resource->roles->pluck('name')),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->resource->permissions->pluck('name')),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
        ];
    }
}
