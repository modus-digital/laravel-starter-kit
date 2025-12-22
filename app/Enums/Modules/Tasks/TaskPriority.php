<?php

declare(strict_types=1);

namespace App\Enums\Modules\Tasks;

enum TaskPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => __('enums.task_priority.low'),
            self::NORMAL => __('enums.task_priority.normal'),
            self::HIGH => __('enums.task_priority.high'),
            self::CRITICAL => __('enums.task_priority.critical'),
        };
    }
}
