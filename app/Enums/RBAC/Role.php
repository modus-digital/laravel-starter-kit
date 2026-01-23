<?php

declare(strict_types=1);

namespace App\Enums\RBAC;

use App\Traits\Enums\HasOptions;

enum Role: string
{
    use HasOptions;

    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case USER = 'user';

    public function getLabel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('enums.rbac.role.super_admin'),
            self::ADMIN => __('enums.rbac.role.admin'),
            self::USER => __('enums.rbac.role.user'),
        };
    }
}
