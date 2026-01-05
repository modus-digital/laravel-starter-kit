<?php

declare(strict_types=1);

namespace App\Enums\Modules\Tasks;

enum TaskViewType: string
{
    case LIST = 'list';
    case KANBAN = 'kanban';
    case CALENDAR = 'calendar';
    case GANTT = 'gantt';
}
