<?php

namespace App\Enums\RBAC;

/**
 * The Role enum represents the different roles that are available
 * in the system by default.
 */
enum Role: string
{
    /**
     * The super admin role.
     */
    case SUPER_ADMIN = 'Super-administrator';

    /**
     * The default role for users.
     */
    case USER = 'Gebruiker';

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
            self::USER => __('auth.rbac.role.user.description'),
        };
    }

    public function displayName(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('auth.rbac.role.super_admin.title'),
            self::USER => __('auth.rbac.role.user.title'),
        };
    }

    public function color(): string
    {
        // bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900 dark:text-blue-300 dark:ring-blue-300/20
        // bg-rose-100 text-rose-800 ring-rose-800/10 dark:bg-rose-900 dark:text-rose-300 dark:ring-rose-300/20
        //
        return match ($this) {
            self::SUPER_ADMIN => 'bg-rose-100 text-rose-800 ring-rose-800/10 dark:bg-rose-900 dark:text-rose-300 dark:ring-rose-300/20',
            self::USER => 'bg-sky-100 text-sky-800 ring-sky-800/10 dark:bg-sky-900 dark:text-sky-300 dark:ring-sky-300/20'
        };
    }
}
