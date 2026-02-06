<?php

declare(strict_types=1);

namespace App\Events\Tasks;

use App\Models\Modules\Tasks\Task;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TaskDueApproaching
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly int $daysUntilDue,
    ) {}
}
