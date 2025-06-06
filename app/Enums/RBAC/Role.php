<?php

namespace App\Enums\RBAC;

/**
 * The Role enum represents the different roles that are available
 * in the system by default.
 */
enum Role: string
{
    case SUPER_ADMIN = 'super-administrator';
    case ADMIN = 'administrator';
    case USER = 'gebruiker';

    /**
     * Get the description for the role.
     *
     * This method returns a description string based on the role.
     * The descriptions are provided in Dutch and explain the permissions
     * and responsibilities associated with each role.
     *
     * @return string The description of the role.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('auth.rbac.role.super_admin.description'),
            self::ADMIN => __('auth.rbac.role.admin.description'),
            self::USER => __('auth.rbac.role.user.description'),
        };
    }

    public function displayName(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('auth.rbac.role.super_admin.title'),
            self::ADMIN => __('auth.rbac.role.admin.title'),
            self::USER => __('auth.rbac.role.user.title'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'bg-rose-100 text-rose-800 ring-rose-800/10 dark:bg-rose-900 dark:text-rose-300 dark:ring-rose-300/20',
            self::ADMIN => 'bg-sky-100 text-sky-800 ring-sky-800/10 dark:bg-sky-900 dark:text-sky-300 dark:ring-sky-300/20',
            self::USER => 'bg-emerald-100 text-emerald-800 ring-emerald-800/10 dark:bg-emerald-900 dark:text-emerald-300 dark:ring-emerald-300/20',
        };
    }

    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $role) => $role->displayName(), self::cases())
        );
    }
}
