<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskView;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTasks
{
    /**
     * @return MorphMany<TaskView, $this>
     */
    public function taskViews(): MorphMany
    {
        return $this->morphMany(related: TaskView::class, name: 'taskable');
    }

    /**
     * @return MorphMany<Task, $this>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(related: Task::class, name: 'taskable');
    }
}
