<?php

declare(strict_types=1);

namespace App\Enums\RBAC;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Get the display label for this role
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('enums.rbac.role.label.super_admin'),
            self::ADMIN => __('enums.rbac.role.label.admin'),
            self::USER => __('enums.rbac.role.label.user'),
        };
    }

    /**
     * Get the description for this role
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('enums.rbac.role.description.super_admin'),
            self::ADMIN => __('enums.rbac.role.description.admin'),
            self::USER => __('enums.rbac.role.description.user'),
        };
    }

    /**
     * Check if this is an internal role that cannot be modified
     */
    public function isInternal(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            self::USER => false,
        };
    }
}
