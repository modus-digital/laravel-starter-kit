<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

final class RoleService
{
    /**
     * Get formatted roles excluding super admin
     *
     * @return Collection<int, array{name: string, label: string}>
     */
    public function getFormattedRoles(): Collection
    {
        return Role::withInternal()
            ->where('name', '!=', Role::SUPER_ADMIN)
            ->get()
            ->map(fn (Role $role): array => [
                'name' => $role->name,
                'label' => $this->resolveRoleLabel($role->name),
            ]);
    }

    /**
     * Get formatted roles for client context (all non-super-admin roles)
     *
     * @return Collection<int, array{id: int, name: string, label: string}>
     */
    public function getFormattedRolesForClient(): Collection
    {
        return Role::query()
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => $this->resolveRoleLabel($role->name),
            ]);
    }

    /**
     * Resolve a role label from translations with fallback
     */
    private function resolveRoleLabel(string $roleName): string
    {
        $key = 'enums.rbac.role.'.$roleName;
        $label = __($key);

        if ($label === $key) {
            return str($roleName)->headline()->toString();
        }

        return $label;
    }
}
