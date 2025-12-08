<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\RBAC\Role as RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Role $resource
 */
final class RoleResource extends JsonResource
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
            'guard_name' => $this->resource->guard_name,
            'icon' => $this->resource->icon,
            'color' => $this->resource->color,
            'is_internal' => $this->isInternalRole(),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->resource->permissions->pluck('name')),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    /**
     * Check if this is an internal/system role that cannot be modified
     */
    private function isInternalRole(): bool
    {
        return in_array($this->resource->name, [
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN->value,
        ]);
    }
}
