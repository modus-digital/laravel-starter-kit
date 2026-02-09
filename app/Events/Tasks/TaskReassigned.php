<?php

declare(strict_types=1);

namespace App\Events\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class TaskReassigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Task $task,
        public ?User $previousAssignee,
        public ?User $newAssignee,
        public User $reassigner,
    ) {}
}
