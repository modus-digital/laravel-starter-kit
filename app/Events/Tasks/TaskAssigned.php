<?php

declare(strict_types=1);

namespace App\Events\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TaskAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly User $assignee,
        public readonly User $assigner,
    ) {}
}
