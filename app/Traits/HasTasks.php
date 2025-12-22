<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Modules\Tasks\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTasks
{
    /**
     * @return MorphMany<Task, $this>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(related: Task::class, name: 'taskable');
    }

    /**
     * Get only incomplete/open tasks
     *
     * @return MorphMany<Task, $this>
     */
    public function openTasks(): MorphMany
    {
        return $this->tasks()->whereNull('completed_at');
    }

    /**
     * Get only completed tasks
     *
     * @return MorphMany<Task, $this>
     */
    public function completedTasks(): MorphMany
    {
        return $this->tasks()->whereNotNull('completed_at');
    }

    /**
     * Get overdue tasks (due date passed and not completed)
     *
     * @return MorphMany<Task, $this>
     */
    public function overdueTasks(): MorphMany
    {
        return $this->tasks()
            ->whereNull('completed_at')
            ->where('due_date', '<', now());
    }

    /**
     * Get tasks due today
     *
     * @return MorphMany<Task, $this>
     */
    public function tasksDueToday(): MorphMany
    {
        return $this->tasks()
            ->whereNull('completed_at')
            ->whereDate('due_date', today());
    }
}
