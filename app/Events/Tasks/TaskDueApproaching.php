<?php

declare(strict_types=1);

namespace App\Events\Tasks;

use App\Models\Modules\Tasks\Task;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class TaskDueApproaching
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Task $task,
        public int $daysUntilDue,
    ) {}
}
