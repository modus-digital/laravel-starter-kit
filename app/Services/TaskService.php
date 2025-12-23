<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Modules\Tasks\TaskViewType;
use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskView;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\Activity;

final class TaskService
{
    /*
    |------------------------------------------------
    |Task views related methods
    |------------------------------------------------
    |
    */
    public function createTaskView(User $user, string $name, string $type = TaskViewType::LIST): TaskView
    {
        $taskView = TaskView::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => $type,
        ]);

        Activity::inLog('tasks')
            ->event('tasks.views.created')
            ->causedBy($user)
            ->performedOn($taskView)
            ->withProperties([
                'task_view' => [
                    'id' => $taskView->id,
                    'name' => $taskView->name,
                ],
            ])
            ->log('Task view created');

        return $taskView;
    }

    public function updateTaskView(TaskView $taskView, string $name, string $type = TaskViewType::LIST): TaskView
    {
        $taskView->update([
            'name' => $name,
            'type' => $type,
        ]);

        Activity::inLog('tasks')
            ->event('tasks.views.updated')
            ->causedBy($taskView->taskable)
            ->performedOn($taskView)
            ->withProperties([
                'task_view' => [
                    'id' => $taskView->id,
                    'name' => $taskView->name,
                    'type' => $taskView->type,
                ],
            ])
            ->log('Task view updated');

        return $taskView;
    }

    /*
    |------------------------------------------------
    |Tasks related methods
    |------------------------------------------------
    |
    */

    /**
     * @return Collection<int, Task>
     */
    public function getAccessibleTasksForUser(User $user, ?string $currentClientId = null): Collection
    {
        /** @var Collection<int, Task> $tasks */
        $tasks = $this->queryAccessibleTasksForUser($user, $currentClientId)->get();

        return $tasks;
    }

    /**
     * Returns a query for all tasks the given user has access to.
     *
     * Access rules:
     * - The task is directly taskable to the user (taskable_type = User::class, taskable_id = $user->id)
     * - The task is taskable to a client the user belongs to (taskable_type = Client::class, taskable_id IN user's client_ids)
     *
     * @return Builder<Task>
     */
    public function queryAccessibleTasksForUser(User $user, ?string $currentClientId = null): Builder
    {
        return $this->baseAccessibleTasksQueryForUser($user)
            ->when(
                $currentClientId,
                fn (Builder $query) => $query
                    ->where('taskable_type', Client::class)
                    ->where('taskable_id', $currentClientId),
            );
    }

    /**
     * @return Builder<Task>
     */
    private function baseAccessibleTasksQueryForUser(User $user): Builder
    {
        return Task::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where(function (Builder $query) use ($user): void {
                    $query
                        ->where('taskable_type', User::class)
                        ->where('taskable_id', $user->getKey());
                })->orWhere(function (Builder $query) use ($user): void {
                    $query
                        ->where('taskable_type', Client::class)
                        ->whereIn('taskable_id', function (QueryBuilder $subQuery) use ($user): void {
                            $subQuery
                                ->select('client_id')
                                ->from('client_users')
                                ->where('user_id', $user->getKey());
                        });
                });
            });
    }
}
