<?php

declare(strict_types=1);

namespace App\Enums;

enum QueuePriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => __('enums.queue_priority.low'),
            self::MEDIUM => __('enums.queue_priority.medium'),
            self::HIGH => __('enums.queue_priority.high'),
        };
    }
}
