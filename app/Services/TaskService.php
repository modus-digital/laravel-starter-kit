<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Modules\Tasks\TaskPriority;
use App\Enums\Modules\Tasks\TaskType;
use App\Enums\Modules\Tasks\TaskViewType;
use App\Events\Tasks\TaskAssigned;
use App\Events\Tasks\TaskCompleted;
use App\Events\Tasks\TaskReassigned;
use App\Events\Tasks\TaskUpdated;
use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\Modules\Tasks\TaskView;
use App\Models\User;
use BackedEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

final class TaskService
{
    public function getStatuses(): Collection
    {
        return TaskStatus::all();
    }

    /*
    |------------------------------------------------
    |Task views related methods
    |------------------------------------------------
    |
    */

    /**
     * @return Collection<int, TaskView>
     */
    public function getTaskViewsForUser(User $user, ?string $currentClientId = null): Collection
    {
        /** @var Collection<int, TaskView> $views */
        $views = $this->queryAccessibleTaskViewsForUser($user, $currentClientId)
            ->with('statuses')
            ->get();

        return $views;
    }

    /**
     * Query accessible task views for a user.
     * When a client is selected, returns BOTH user-scoped views AND views for that specific client.
     *
     * @return Builder<TaskView>
     */
    public function queryAccessibleTaskViewsForUser(User $user, ?string $currentClientId = null): Builder
    {
        if ($currentClientId !== null) {
            // Show both user-scoped views and views for the selected client
            return TaskView::query()
                ->where(function (Builder $query) use ($user, $currentClientId): void {
                    // User-scoped views
                    $query->where(function (Builder $query) use ($user): void {
                        $query
                            ->where('taskable_type', User::class)
                            ->where('taskable_id', $user->getKey());
                    })
                    // Client-scoped views for the selected client
                        ->orWhere(function (Builder $query) use ($currentClientId): void {
                            $query
                                ->where('taskable_type', Client::class)
                                ->where('taskable_id', $currentClientId);
                        });
                });
        }

        // No client selected: only show user-scoped views
        return $this->baseAccessibleTaskViewsQueryForUser($user);
    }

    /**
     * Create a new task view.
     * When a client is selected, the view is scoped to that client. Otherwise, it's user-scoped.
     *
     * @param  array<int, string>  $statusIds
     */
    public function createTaskView(
        User $user,
        string $name,
        TaskViewType $type = TaskViewType::LIST,
        array $statusIds = [],
        ?string $currentClientId = null
    ): TaskView {
        // Determine scope: client if selected, otherwise user
        $taskableType = $currentClientId !== null ? Client::class : User::class;
        $taskableId = $currentClientId ?? $user->id;

        $taskView = TaskView::create([
            'taskable_type' => $taskableType,
            'taskable_id' => $taskableId,
            'name' => $name,
            'slug' => Str::slug("{$name}-{$type->value}-{$taskableId}"),
            'type' => $type,
        ]);

        // Sync selected statuses (columns) for this view
        if ($statusIds !== []) {
            $syncData = [];
            foreach ($statusIds as $position => $statusId) {
                $syncData[$statusId] = ['position' => $position];
            }
            $taskView->statuses()->sync($syncData);
        }

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

    /**
     * Rename a task view.
     */
    public function renameTaskView(TaskView $taskView, string $name): TaskView
    {
        $oldName = $taskView->name;

        $taskView->update([
            'name' => $name,
            'slug' => Str::slug("{$name}-{$taskView->type->value}-{$taskView->taskable_id}"),
        ]);

        Activity::inLog('tasks')
            ->event('tasks.views.renamed')
            ->causedBy($taskView->taskable)
            ->performedOn($taskView)
            ->withProperties([
                'task_view' => [
                    'id' => $taskView->id,
                    'old_name' => $oldName,
                    'new_name' => $taskView->name,
                ],
            ])
            ->log('Task view renamed');

        return $taskView;
    }

    /**
     * Update the statuses (columns) for a task view.
     *
     * @param  array<int, string>  $statusIds
     */
    public function updateTaskViewStatuses(TaskView $taskView, array $statusIds): TaskView
    {
        $syncData = [];
        foreach ($statusIds as $position => $statusId) {
            $syncData[$statusId] = ['position' => $position];
        }
        $taskView->statuses()->sync($syncData);

        // Reload the statuses relationship
        $taskView->load('statuses');

        Activity::inLog('tasks')
            ->event('tasks.views.statuses_updated')
            ->causedBy($taskView->taskable)
            ->performedOn($taskView)
            ->withProperties([
                'task_view' => [
                    'id' => $taskView->id,
                    'name' => $taskView->name,
                    'status_count' => count($statusIds),
                ],
            ])
            ->log('Task view statuses updated');

        return $taskView;
    }

    /**
     * Set a task view as the default for its scope.
     * Unsets the default flag on other views in the same taskable scope.
     */
    public function setDefaultTaskView(TaskView $taskView): void
    {
        // Unset default for all other views in the same taskable scope
        TaskView::query()
            ->where('taskable_type', $taskView->taskable_type)
            ->where('taskable_id', $taskView->taskable_id)
            ->where('id', '!=', $taskView->id)
            ->update(['is_default' => false]);

        // Set this view as default
        $taskView->update(['is_default' => true]);

        Activity::inLog('tasks')
            ->event('tasks.views.set_default')
            ->causedBy($taskView->taskable)
            ->performedOn($taskView)
            ->withProperties([
                'task_view' => [
                    'id' => $taskView->id,
                    'name' => $taskView->name,
                ],
            ])
            ->log('Task view set as default');
    }

    /**
     * Delete (soft delete) a task view.
     */
    public function deleteTaskView(TaskView $taskView): void
    {
        $taskViewData = [
            'id' => $taskView->id,
            'name' => $taskView->name,
        ];

        $causer = $taskView->taskable;

        $taskView->delete();

        Activity::inLog('tasks')
            ->event('tasks.views.deleted')
            ->causedBy($causer)
            ->withProperties([
                'task_view' => $taskViewData,
            ])
            ->log('Task view deleted');
    }

    public function updateTaskView(TaskView $taskView, string $name, TaskViewType $type): TaskView
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
     * Create a new task.
     *
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     type?: string|null,
     *     priority?: string|null,
     *     status_id?: string|null,
     *     order?: int|null,
     *     due_date?: string|null,
     *     assigned_to_id?: string|null,
     * }  $data
     *
     * @throws AuthorizationException
     */
    public function createNewTask(User $user, array $data, ?string $currentClientId = null): Task
    {
        if ($currentClientId !== null && ! $user->clients()->whereKey($currentClientId)->exists()) {
            throw new AuthorizationException('You do not have access to this client.');
        }

        $taskableType = $currentClientId !== null ? Client::class : User::class;
        $taskableId = $currentClientId ?? $user->getKey();

        $statusId = $data['status_id'] ?? $this->getDefaultStatusId();

        $status = TaskStatus::query()->find($statusId);
        if ($status === null) {
            throw ValidationException::withMessages([
                'status_id' => ['The selected status is invalid.'],
            ]);
        }

        $task = Task::query()->create([
            'taskable_type' => $taskableType,
            'taskable_id' => $taskableId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => TaskType::tryFrom($data['type'] ?? '') ?? TaskType::TASK,
            'priority' => TaskPriority::tryFrom($data['priority'] ?? '') ?? TaskPriority::NORMAL,
            'status_id' => $statusId,
            'order' => $data['order'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'completed_at' => $this->shouldMarkCompleted($status) ? now() : null,
            'created_by_id' => $user->getKey(),
            'assigned_to_id' => $data['assigned_to_id'] ?? null,
        ]);

        // Load relationships for event dispatching
        $task->load(['assignedTo', 'createdBy', 'status']);

        Activity::inLog('tasks')
            ->event('tasks.created')
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties([
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'taskable_type' => $task->taskable_type,
                    'taskable_id' => $task->taskable_id,
                ],
            ])
            ->log('Task created');

        // Dispatch TaskAssigned event if task is assigned
        if ($task->assignedTo !== null) {
            Event::dispatch(new TaskAssigned(
                task: $task,
                assignee: $task->assignedTo,
                assigner: $user,
            ));
        }

        // Dispatch TaskCompleted event if task is completed
        if ($task->completed_at !== null) {
            Event::dispatch(new TaskCompleted(
                task: $task,
                completedBy: $user,
            ));
        }

        return $task;
    }

    /**
     * Update a task.
     *
     * @param  array{
     *     title?: string,
     *     description?: string|null,
     *     type?: string|null,
     *     priority?: string|null,
     *     status_id?: string|null,
     *     order?: int|null,
     *     due_date?: string|null,
     *     assigned_to_id?: string|null,
     * }  $data
     *
     * @throws AuthorizationException
     */
    public function updateTask(User $user, Task $task, array $data, ?string $currentClientId = null): Task
    {
        $this->ensureUserCanAccessTask($user, $task, $currentClientId);

        // Capture original values before updating
        $originalValues = $this->getTaskOriginalValues($task);

        $updateData = [];
        $status = null;

        if (array_key_exists('title', $data)) {
            $updateData['title'] = $data['title'];
        }

        if (array_key_exists('description', $data)) {
            $updateData['description'] = $data['description'];
        }

        if (array_key_exists('type', $data)) {
            $typeValue = $data['type'];
            if ($typeValue instanceof TaskType) {
                $updateData['type'] = $typeValue;
            } else {
                $updateData['type'] = TaskType::tryFrom((string) ($typeValue ?? '')) ?? $task->type;
            }
        }

        if (array_key_exists('priority', $data)) {
            $priorityValue = $data['priority'];
            if ($priorityValue instanceof TaskPriority) {
                $updateData['priority'] = $priorityValue;
            } else {
                $updateData['priority'] = TaskPriority::tryFrom((string) ($priorityValue ?? '')) ?? $task->priority;
            }
        }

        if (array_key_exists('status_id', $data) && $data['status_id'] !== null) {
            $status = TaskStatus::query()->find($data['status_id']);
            if ($status === null) {
                throw ValidationException::withMessages([
                    'status_id' => ['The selected status is invalid.'],
                ]);
            }

            $updateData['status_id'] = $status->getKey();
        }

        if (array_key_exists('order', $data)) {
            $updateData['order'] = $data['order'];
        }

        if (array_key_exists('due_date', $data)) {
            $updateData['due_date'] = $data['due_date'];
        }

        if (array_key_exists('assigned_to_id', $data)) {
            $updateData['assigned_to_id'] = $data['assigned_to_id'] === '' ? null : $data['assigned_to_id'];
        }

        if ($status !== null) {
            $updateData['completed_at'] = $this->shouldMarkCompleted($status) ? ($task->completed_at ?? now()) : null;
        }

        // Detect actual changes before updating (only field names and old values)
        $changedFields = $this->detectChangedFields($task, $updateData, $originalValues);

        // Capture assignment changes before update
        $previousAssignee = $task->assignedTo;
        $wasCompleted = $task->completed_at !== null;

        // Update the task
        $task->update($updateData);

        // Reload relationships for accurate logging
        $task->refresh();
        $task->load(['status', 'assignedTo', 'createdBy']);

        // Get new values after update
        $newValues = $this->getTaskOriginalValues($task);

        // Log each change individually with properly formatted values
        foreach ($changedFields as $field) {
            $oldFormatted = $this->formatValueForLogging(
                $originalValues[$field] ?? null,
                $field,
                $originalValues
            );
            $newFormatted = $this->formatValueForLogging(
                $newValues[$field] ?? null,
                $field,
                $newValues,
                $task
            );

            $this->logTaskChange($user, $task, [
                'field' => $field,
                'old' => $oldFormatted,
                'new' => $newFormatted,
                'event' => $this->getEventTypeForField($field),
            ]);
        }

        // Dispatch events based on changes
        $changesForEvent = [];
        foreach ($changedFields as $field) {
            $changesForEvent[$field] = [
                'old' => $originalValues[$field] ?? null,
                'new' => $newValues[$field] ?? null,
            ];
        }

        // Dispatch TaskReassigned event if assignment changed
        if (in_array('assigned_to_id', $changedFields, true)) {
            $newAssignee = $task->assignedTo;
            if ($previousAssignee?->id !== $newAssignee?->id) {
                Event::dispatch(new TaskReassigned(
                    task: $task,
                    previousAssignee: $previousAssignee,
                    newAssignee: $newAssignee,
                    reassigner: $user,
                ));
            }
        }

        // Dispatch TaskCompleted event if task was just completed
        if (in_array('status_id', $changedFields, true) && ! $wasCompleted && $task->completed_at !== null) {
            Event::dispatch(new TaskCompleted(
                task: $task,
                completedBy: $user,
            ));
        }

        // Dispatch TaskUpdated event if there are any changes (excluding assignment/completion which have their own events)
        $updateChanges = [];
        foreach ($changesForEvent as $field => $change) {
            if ($field !== 'assigned_to_id' && $field !== 'status_id') {
                $updateChanges[$field] = $change;
            }
        }

        if (! empty($updateChanges)) {
            Event::dispatch(new TaskUpdated(
                task: $task,
                updatedBy: $user,
                changes: $updateChanges,
            ));
        }

        return $task;
    }

    /**
     * Delete (soft delete) a task.
     *
     * @throws AuthorizationException
     */
    public function deleteTask(User $user, Task $task, ?string $currentClientId = null): void
    {
        $this->ensureUserCanAccessTask($user, $task, $currentClientId);

        $taskData = [
            'id' => $task->id,
            'title' => $task->title,
        ];

        $task->delete();

        Activity::inLog('tasks')
            ->event('tasks.deleted')
            ->causedBy($user)
            ->withProperties([
                'task' => $taskData,
            ])
            ->log('Task deleted');
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureUserCanAccessTask(User $user, Task $task, ?string $currentClientId = null): void
    {
        $canAccess = $this->queryAccessibleTasksForUser($user, $currentClientId)
            ->whereKey($task->getKey())
            ->exists();

        if (! $canAccess) {
            throw new AuthorizationException('You do not have access to this task.');
        }
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

    private function getDefaultStatusId(): string
    {
        $todo = TaskStatus::query()
            ->whereRaw('lower(name) = ?', ['todo'])
            ->first();

        if ($todo !== null) {
            return $todo->getKey();
        }

        return TaskStatus::findOrCreateByName('Todo')->getKey();
    }

    private function shouldMarkCompleted(TaskStatus $status): bool
    {
        return mb_strtolower($status->name) === 'done';
    }

    /**
     * Get original values from the task for comparison.
     *
     * @return array<string, mixed>
     */
    private function getTaskOriginalValues(Task $task): array
    {
        $task->load(['status', 'assignedTo']);

        return [
            'title' => $task->title,
            'description' => $task->description,
            'type' => $task->type,
            'priority' => $task->priority,
            'status_id' => $task->status_id,
            'status_name' => $task->status?->name,
            'status_color' => $task->status?->color,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'assigned_to_id' => $task->assigned_to_id,
            'assigned_to_name' => $task->assignedTo?->name,
        ];
    }

    /**
     * Detect which fields actually changed.
     *
     * @param  array<string, mixed>  $updateData
     * @param  array<string, mixed>  $originalValues
     * @return array<int, string>
     */
    private function detectChangedFields(Task $task, array $updateData, array $originalValues): array
    {
        $changedFields = [];

        foreach ($updateData as $field => $newValue) {
            $oldValue = $originalValues[$field] ?? null;

            // Normalize values for comparison
            $oldNormalized = $this->normalizeValueForComparison($oldValue, $field);
            $newNormalized = $this->normalizeValueForComparison($newValue, $field);

            // Skip if values are the same
            if ($oldNormalized === $newNormalized) {
                continue;
            }

            $changedFields[] = $field;
        }

        return $changedFields;
    }

    /**
     * Normalize a value for comparison purposes.
     */
    private function normalizeValueForComparison(mixed $value, string $field): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle date fields
        if ($field === 'due_date' && $value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        // Handle enum fields
        if (in_array($field, ['type', 'priority'], true) && $value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    /**
     * Get the event type for a specific field change.
     */
    private function getEventTypeForField(string $field): string
    {
        return match ($field) {
            'title' => 'tasks.title_changed',
            'description' => 'tasks.description_changed',
            'status_id' => 'tasks.status_changed',
            'priority' => 'tasks.priority_changed',
            'type' => 'tasks.type_changed',
            'due_date' => 'tasks.due_date_changed',
            'assigned_to_id' => 'tasks.assigned_changed',
            default => 'tasks.field_changed',
        };
    }

    /**
     * Format a value for logging display.
     *
     * @param  array<string, mixed>  $context
     */
    private function formatValueForLogging(mixed $value, string $field, array $context, ?Task $task = null): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field) {
            'status_id' => [
                'id' => $value,
                'name' => $context['status_name'] ?? ($task?->status?->name),
                'color' => $context['status_color'] ?? ($task?->status?->color),
            ],
            'assigned_to_id' => [
                'id' => $value,
                'name' => $context['assigned_to_name'] ?? ($task?->assignedTo?->name),
            ],
            'priority' => $value instanceof TaskPriority ? [
                'value' => $value->value,
                'label' => $value->getLabel(),
            ] : (is_string($value) ? [
                'value' => $value,
                'label' => TaskPriority::tryFrom($value)?->getLabel() ?? $value,
            ] : $value),
            'type' => $value instanceof TaskType ? [
                'value' => $value->value,
                'label' => $value->getLabel(),
            ] : (is_string($value) ? [
                'value' => $value,
                'label' => TaskType::tryFrom($value)?->getLabel() ?? $value,
            ] : $value),
            'due_date' => $value instanceof \Carbon\Carbon ? $value->format('Y-m-d') : (is_string($value) ? $value : $value),
            default => $value,
        };
    }

    /**
     * Log a single task field change.
     *
     * @param  array{field: string, old: mixed, new: mixed, event: string}  $change
     */
    private function logTaskChange(User $user, Task $task, array $change): void
    {
        $properties = [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
            ],
            'field' => $change['field'],
            'old' => $change['old'],
            'new' => $change['new'],
        ];

        // Handle special cases for better translation
        if ($change['field'] === 'assigned_to_id') {
            if ($change['old'] === null && $change['new'] !== null) {
                $change['event'] = 'tasks.assigned';
                unset($properties['old']);
            } elseif ($change['old'] !== null && $change['new'] === null) {
                $change['event'] = 'tasks.unassigned';
                unset($properties['new']);
            } else {
                $change['event'] = 'tasks.reassigned';
            }
        } elseif ($change['field'] === 'due_date') {
            if ($change['old'] === null && $change['new'] !== null) {
                $change['event'] = 'tasks.due_date_set';
                unset($properties['old']);
            } elseif ($change['old'] !== null && $change['new'] === null) {
                $change['event'] = 'tasks.due_date_removed';
                unset($properties['new']);
            }
        }

        Activity::inLog('tasks')
            ->event($change['event'])
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties($properties)
            ->log('Task field changed');
    }

    /**
     * @return Builder<TaskView>
     */
    private function baseAccessibleTaskViewsQueryForUser(User $user): Builder
    {
        return TaskView::query()
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
