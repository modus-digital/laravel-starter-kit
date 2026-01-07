<?php

declare(strict_types=1);

namespace App\Events\Comments;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CommentAdded
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $comment
     */
    public function __construct(
        public readonly Task $task,
        public readonly User $commenter,
        public readonly array $comment,
    ) {}
}
