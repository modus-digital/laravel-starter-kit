<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enums\HasOptions;

enum ActivityStatus: string
{
    use HasOptions;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case DELETED = 'deleted';

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::SUSPENDED => 'yellow',
            self::INACTIVE, self::DELETED => 'red',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => __('enums.activity_status.active'),
            self::INACTIVE => __('enums.activity_status.inactive'),
            self::SUSPENDED => __('enums.activity_status.suspended'),
            self::DELETED => __('enums.activity_status.deleted'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-o-check-circle',
            self::INACTIVE => 'heroicon-o-x-circle',
            self::SUSPENDED => 'heroicon-o-shield-exclamation',
            self::DELETED => 'heroicon-o-trash',
        };
    }
}
