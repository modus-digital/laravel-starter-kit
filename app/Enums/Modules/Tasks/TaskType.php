<?php

declare(strict_types=1);

namespace App\Enums\Modules\Tasks;

enum TaskType: string
{
    case TASK = 'task';
    case BUG = 'bug';
    case FEATURE = 'feature';
    case DOCUMENTATION = 'documentation';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::TASK => __('enums.task_type.task'),
            self::BUG => __('enums.task_type.bug'),
            self::FEATURE => __('enums.task_type.feature'),
            self::DOCUMENTATION => __('enums.task_type.documentation'),
            self::OTHER => __('enums.task_type.other'),
        };
    }
}
